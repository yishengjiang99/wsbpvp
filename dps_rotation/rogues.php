<?php
    require_once("../functions.php");

    $spells=['mutilate','garrotte','rupture','envenom','toxicblade'];
    $initial_damage=[2000,0,0,6000,4000];
    $damage_over_time=[0,400,500,0,0];
    $dot_frequency=[0,3,3,0,0];
    $dot_tick_energy_regen=[0,7,7,0,0];
    $dot_tick_combo_point_regen=[0,1,1,0,0];
    $initial_damage_combo_point_regen=[2,1,0,0,0,1];
    $spell_energy_cost=[30,30,30,30,30];
    $spell_cp_cost=[0,0,5,5,0];
    $dot_default_duration=[0,18,8,0,0];
    $dot_duration_per_cp_spent=[0,0,3,0,0];
    $spell_additional_damage_per_cp_spent=[0,0,0,100,0];
    $initial_energy=150;

    $t=0;
    $sequence = [1,2,2,1,3,4]; //mutilate,garrote, garrote, mutilate, rupture, evenom
    $ntime=10000;
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
                $total_energy += $dot_tick_energy_regen[$spell];
                $active_combo_points += $dot_tick_combo_point_regen[$spell];
            }
        }
        
        $spell=$sequence[$sequence_index % count($sequence)];
        $spell_name=$spells[$spell];
        
        $can_cast = $active_combo_points >= $spell_cp_cost[$spell] && $energy >= $spell_energy_cost[$spell];

        if($can_cast){
            $sequence_index++;
            $energy -= $spell_energy_cost[$spell];

            $damageDone=$initial_damage[$spell];
            if($spell_cp_cost[$spell]){
                $combo_point_spent=$active_combo_points;
                $damageDone+= $spell_additional_damage_per_cp_spent[$spell] * $combo_point_spent;
            }
        
            $totalDamage+=$damageDone;            

            if($dot_default_duration[$spell]){
                $dot_duration_added = $dot_default_duration[$spell]+$dot_duration_per_cp_spent*$combo_point_spent;
                $existing_dot=$active_dots[$spell];
                if($existing) $remaining_duration = min(0.3*$dot_duration[$spell], $existing_dot['remaining_duration'])+$dot_duration_added;
                else $remaining_duration=$dot_duration_added;
                $active_dots[$spell]=[
                    'remaining_duration'=>$remaining_duration,
                ];
            }

        }
        echo_line("Casting $spellname as time $t");



    }  
