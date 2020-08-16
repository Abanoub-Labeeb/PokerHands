<?php

namespace App\Http\helpers;

class ResultCheck {
    

    
/**
 * 1- Test a royal straight flush.
 */
public static function isRoyalFlush($hand) {
    
    if($hand->isRoyal() && $hand->isFlush()){
       return 1;   
    }else{
       return 0;
    }   
}
    
/**
 *2- Test a straight flush.
 */
public static function isStraightFlush($hand) {
    
    if($hand->isFlush() && $hand->isStraight()){
        return 1;
    }else {
        return 0;
    }
   
}

/**
 *3- Test four of a kind.
 */
public static function isFourOfAKind($hand) {
    
    if(!array_key_exists("four",$hand->sets))
        return 0;
    
    // 4 of a Kind
    $hand->setSets()
         ->setRank();
    
    if($hand->sets['four'] !== 0){
        return 1;
    }else{
        return 0;
    }
    
}


/**
 *4- Test a full house
 */
public static function isFullHouse($hand) {
    
    if(!array_key_exists("pair",$hand->sets) || !array_key_exists("three",$hand->sets))
        return 0;
    
    $hand->setSets()
         ->setRank();
    
    return (count($hand->sets['pair']) == 0) && ($hand->sets['three'] !== 0);

}

/**
 *5- Test flushes
 */
public static function isFlush($hand) {
    $_IsFlush = $hand->isFlush();
    return $_IsFlush;
}


/**
 *6- Test a random straight.
 */
public static function isStraight($hand) {
    
    $hand->setSets();
    $_IsStraight = $hand->isStraight();
    
    return $_IsStraight;
}


/*
 *7- Test 3 of A  Kind.
*/
public static function  isThreeOfAKind($hand){
    
    if(!array_key_exists("three",$hand->sets))
        return 0;
    
    // 3 of a Kind   
    $hand->setSets()
         ->setRank();
    
     if($hand->sets['three'] !== 0){
          return 1;     
     }else{
          return 0;
     }
    
}

/**
 *8- Test 2 Pair.
 */
public static function isTwoPair($hand){
    
    if(!array_key_exists("pair",$hand->sets))
        return 0;
  
    // 2 Pair.    
    $hand->setSets()
         ->setRank();
    
    $hand_output = $hand->__toString();
    
    if(count($hand->sets['pair']) == 2){
        return 1;
    }else {
        return 0;
    }
    
}


/**
 *9- Test 1 Pair.
*/
public static function  isOnePair($hand){
    
    if(!array_key_exists("pair",$hand->sets))
        return 0;
    
    // Pair.
    $hand->setSets()
         ->setRank();
    
    $hand_output = $hand->__toString();
    
    if(count($hand->sets['pair']) == 1){
       return 1;   
    }else{
       return 0; 
    }    
    
}

/**
 *10- Test High Card.
 */
/**
 * Test functionality to grab the highest ranked card in a hand.
 */
public static function grapHighCard($hand) {

    $high_card = $hand->getHighCard($hand->cards);
    
    return $high_card ;
}


}
