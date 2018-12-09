<?php
    require_once("../functions.php");
    $spells=['mutilate','garrotte','rupture','envenom','toxicblade'];

    $sequence =[1,0,2,4,0,0,3]; //garrote, mutilate, rupture, toxicblde, mutilate, multilate, envenom
    
    $GLOBAL_LOG_LEVEL='debugg';

    $dps=simulateCastSqeuence($sequence,60);
    echo showIframe("timeseries.php",['timeseries'=>$dps],'rogue dps');

    function simulateCastSqeuence($sequence,$ntime=20){
        $spells=['mutilate','garrotte','rupture','envenom','toxicblade'];
        $initial_damage=[2000,0,0,6000,4000];
        $damage_over_time=[0,400,500,0,0];
        $dot_frequencies=[0,3,3,0,0];
        $dot_tick_energy_regen=[0,7,7,0,0];    
        $initial_damage_combo_point_regen=[2,1,0,0,0,1];
        $initial_damage_combo_point_regen_with_shrouded_suffocation=[0,2,0,0,0,0];
        $spell_energy_cost=[30,30,30,30,30];
        $spell_cp_cost=[0,0,5,5,0];
        $dot_default_duration=[0,18,8,0,0];
        $dot_duration_per_cp_spent=[0,0,3,0,0];
        $spell_additional_damage_per_cp_spent=[0,0,0,100,0];
        $spell_cooldown = [1,6,1,1,25];
        $max_energy=150;
        $initial_energy=$max_energy;

        $t=0;
        $spell_last_cast =[];
        $default_energy_regen=10;
        $totalDamage=0;
        $energy=$initial_energy;
        $sequence_index=0;     
        $active_dots=[];
        $active_combo_points=0;
        $totalDamageTimeSeries=[];
        for($t=0;$t<$ntime;$t++){
            $lastTotalData=$totalDamage;
            $totalDamageTimeSeries[$t+1]=$totalDamage;
            echo_line("Time $t:");
            $energy+=$default_energy_regen;
            if($energy>$max_energy) $energy=$max_energy;
            foreach($active_dots as $spell=>&$dot){
                if($dot['remaining_duration'] > 0){
                    $dot['remaining_duration']--;
                }else{
                    continue;
                }
                $dot_frequency = $dot_frequencies[$spell];
                $dot_ticking = $dot_frequency!==0 && $t % $dot_frequency === 0;
                if($dot_ticking) {
                    $spell_name = $spells[$spell];
                    echo_line("Dot tick on $spell_name");
                    $totalDamage += $damage_over_time[$spell];
                    $energy += $dot_tick_energy_regen[$spell];
                    echo_line("Doing ".$damage_over_time[$spell]." damage from dot tick.");
                    echo_line("Gaining ".$dot_tick_energy_regen[$spell]." genergy from dot tick");
                }
            }
    
            $spell=$sequence[$sequence_index % count($sequence)];
            $spell_name=$spells[$spell];
            $spell_on_cooldown=true;
            $has_enough_combo_points=false;

            for($tries=0;$tries<10;$tries++){
                echo_line("Trying to cast $spell_name, checking spell cooldown");
                $spell_on_cooldown = isset($spell_last_cast[$spell]) 
                    && (($t-$spell_last_cast[$spell]) >= $spell_cooldown[$spell]);     
                $has_enough_combo_points = $active_combo_points >= $spell_cp_cost[$spell];
                if($spell_on_cooldown || !$has_enough_combo_points){
                    echo_line("$spell_name is on cooldown, checking next");
                    $sequence_index++;
                    $spell=$sequence[$sequence_index % count($sequence)];
                    $spell_name=$spells[$spell];
                }else{
                    break;
                }
            }
    
            echo_line("Trying to cast $spell_name, checking energy and CP");

            $can_cast = $has_enough_combo_points 
                        && !$spell_on_cooldown
                        && $energy >= $spell_energy_cost[$spell];
                        
            if(!$can_cast){
                echo_line("Cannot cast $spell_name at $energy energy and $active_combo_points cp");
            }
    
            if($can_cast){
                $sequence_index++;
                $energy -= $spell_energy_cost[$spell];

                echo_line("\nCasting $spell_name as time $t");
                $spell_last_cast[$spell]=$t;
    
    
                $damageDone=$initial_damage[$spell];
                if($spell_cp_cost[$spell]){
                    $combo_point_spent=$active_combo_points;
                    $damageDone+= $spell_additional_damage_per_cp_spent[$spell] * $combo_point_spent;
                }else{
                    $combo_point_spent=0;
                }
            
                $totalDamage+=$damageDone;    
                
                $active_combo_points+=$initial_damage_combo_point_regen[$spell];

                echo_line("Doing $damageDone damage for total of $totalDamage");
    
                echo_line("Ganning ".$initial_damage_combo_point_regen[$spell]." from $spell_name");
    
                if($dot_default_duration[$spell]){
                    $dot_duration_added = $dot_default_duration[$spell]+$dot_duration_per_cp_spent[$spell]*$combo_point_spent;
                    if(isset($active_dots[$spell])) {
                        $existing_dot=$active_dots[$spell];
                        $remaining_duration = min(0.3*$existing_dot['original_duration'], $existing_dot['remaining_duration'])+$dot_duration_added;
                    }
                    else {
                        $remaining_duration=$dot_duration_added;
                    }
                    $active_dots[$spell]=[
                        'remaining_duration'=>$remaining_duration,
                        'original_duration'=>$dot_duration_added
                    ];
                }
            } //end of $can_cast
        } //end for-loop
        return $totalDamageTimeSeries;     
    }
   
