<?php
    require_once("../functions.php");

    $spells=['mutilate','garrotte','rupture','envenom','toxicblade'];
    $initial_damage=[2000,0,0,6000,4000];
    $damage_over_time=[0,400,500,0,0];
    $dot_frequency=[0,3,3,0,0];
    $dot_tick_energy_regen=[0,7,7,0,0];
    $dot_tick_combo_point_regen=[0,1,1,0,0];
    $initial_damage_combo_point_regen=[2,1,0,0,0,1];
    $initial_damage_combo_point_regen_with_shrouded_suffocation=[0,2,0,0,0,0];
    $spell_energy_cost=[30,30,30,30,30];
    $spell_cp_cost=[0,0,5,5,0];
    $dot_default_duration=[0,18,8,0,0];
    $dot_duration_per_cp_spent=[0,0,3,0,0];
    $spell_additional_damage_per_cp_spent=[0,0,0,100,0];
    $spell_cooldown = [1,6,1,1,25];
    $initial_energy=150;


    $sequence =[1,0,2,4,1,1,3]; 
    $t=0;
    $spell_last_cast =[];
    $ntime=20;
    $default_energy_regen=10;
    $totalDamage=0;
    $energy=$initial_energy;
    $sequence_index=0;     
    $active_dots=[];
    $active_combo_points=0;

    for($t=0;$t<$ntime;$t++){
        $energy+=$default_energy_regen;
        foreach($active_dots as $spell=>&$dot){
            if($dot['remaining_duration'] > 0){
                $dot['remaining_duration']--;
            }else{
                continue;
            }
            $dot_ticking = $t % $dot_frequency === 0;
            if($dot_ticking) {
                $damage_over_time[$spell];
                $energy += $dot_tick_energy_regen[$spell];
                $active_combo_points += $dot_tick_combo_point_regen[$spell];
            }
        }

        $spell=$sequence[$sequence_index % count($sequence)];
        $spell_name=$spells[$spell];
 
        $spell_on_cooldown=true;


        for($tries=0;$tries<10;$tries++){
            echo "\nTrying to cast $spell_name, checking spell cooldown";
            $spell_on_cooldown = isset($spell_last_cast[$spell]) 
                && (($t-$spell_last_cast[$spell]) >= $spell_cooldown[$spell]);     
            
            if($spell_on_cooldown){
                echo "\n$spell_name is on cooldown, checking next";
                $sequence_index++;
                $spell=$sequence[$sequence_index % count($sequence)];
                $spell_name=$spells[$spell];
            }else{
                break;
            }
        }

        echo "\nTrying to cast $spell_name, checking energy and CP";

        $can_cast = $active_combo_points >= $spell_cp_cost[$spell] 
                    && $energy >= $spell_energy_cost[$spell]
                    && $spell_on_cooldown===false;
                    
        if(!$can_cast){
            echo "\nCannot cast $spell_name at $energy energy and $active_combo_points";
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

            echo "\nGanning ".$initial_damage_combo_point_regen[$spell]." from $spell_name";


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
        }
        



    }  
