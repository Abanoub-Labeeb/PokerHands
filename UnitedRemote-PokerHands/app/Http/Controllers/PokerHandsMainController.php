<?php

namespace App\Http\Controllers;

use App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PokerHandsMainController extends Controller
{
    
    /*
     * Human-readable names of hand ranks.
     */
    static public $ranks = array(
        1 => 'Royal Flush',
        2 => 'Straight Flush',
        3 => 'Four Of A Kind',
        4 => 'Full House',
        5 => 'Flush',
        6 => 'Straight',
        7 => 'Three Of A Kind',
        8 => 'Two Pair',
        9 => 'One Pair',
        10 =>'High Card',
    );
    
    public $result = "" ;
    public $firstPlayerWinTimes = 0; 
    
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(){
        return  view('pokerhands');
    }
    
    /*
     * submit file for system to upload , write to the DB , analyse and return back the result
    */
    
    public  function  submitPokerHands(Request $request){
        
        $file = $request->file('text');
        if($file == null){
           return redirect('/');
        }
        
        $filePath = $this->upload($request);
        if($filePath != null || !empty($filePath)){
            $this->writeToDB($filePath);
            $this->readPokerHandsAndAnalyse();
            
        }
    }
    
    
    public  function  upload(Request $request){
        
        $file = $request->file('text');
        $destinationPath = storage_path().'\uploads';
        $filePath = $destinationPath.'\\'.$file->getClientOriginalName();
        $imageFileType  = $file->getMimeType();
        $uploadOk = 1;
        //temp file data
        $tmpFileName      = $file->getClientOriginalName();
        $tmpFileExtension = $file->getClientOriginalExtension();
        $tmpFileRealPath  = $file->getRealPath();
        $tmpFileSize      = $file->getSize();
        $tmpFileMimeType  = $file->getMimeType();
        
        // Check if file already exists
        if (file_exists($imageFileType)) {
            //delete the file
            unlink($filePath);
            $uploadOk = 0;
        }
        
        // Check file size
        if ($file->getSize() > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        if(empty(strchr($imageFileType,"text"))) {
            echo "Sorry, only txt files are allowed.";
            $uploadOk = 0;
        }
        
        
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "<br/>";
            echo "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
        }else {
            /*
             * this gonna write the file to the root directory/storage/uploads
             * if we want to write it to  pre-defined directory or even define yours and write to it
             * refer to https://laravel.com/docs/7.x/filesystem#the-local-driver
             */
            if ($file->move($destinationPath,$file->getClientOriginalName())) {
                
                return $filePath;
            
            } else {
                
                echo "Sorry, there was an error uploading your file.";
            
            }
        }
        
        
    }
    
    /*
     * Write the poker hand records to DB
     * not the ideal way to write to a file then reading from it and then write to db
     * but this is to expose file handling knowledge 
    */
    public function writeToDB($FilePath){
        
        //delete existing from DB
        DB::table('poker_hands')->delete();
        
        $file = fopen($FilePath, "r") or die("Unable to open file!");
        if ($file) {
            
        while (($line = fgets($file)) !== false) {
            
            // process the line read.
            //using Query Builder
            DB::table('poker_hands')->insert([
                [
                    'cards_distribute' => $line,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ]
            ]);

        }

        fclose($file);
            
        } else {
            // error opening the file.
        }
    }
    
    /*
     * Insert the poker hand records into DB
    */
    public function readPokerHandsAndAnalyse(){
        
        $pokerHands = App\PokerHands::all();
        $lineCards = [];
        $firstPlayerCards  = [];
        $secondPlayerCards = [];
        
        foreach ($pokerHands as $distributeRound) {
            $lineCards = explode(' ' , $distributeRound->cards_distribute);
            
            for ($i = 0; $i < 10; $i++) {
                if($i < 5){
                    $firstPlayerCards[$i]  = $lineCards[$i];
                }else{
                    $secondPlayerCards[$i - 5] = $lineCards[$i];
                }
            }
            
            $this->result = $this->result . '<br/> ********************************************** <br/>';
            $this->analyseLineResults($firstPlayerCards , $secondPlayerCards);
            $this->result = $this->result . '<br/> ********************************************** <br/>';
            //break;
        }
        
        echo '<br/> ********************************************** <br/>';
        echo '<h1>Player A won : '.$this->firstPlayerWinTimes.'Times <h1/>';
        echo '<br/> ********************************************** <br/>';
        echo $this->result;
        
    }
    
    
    public function analyseLineResults($firstPlayerCards , $secondPlayerCards){
       $firstPlayerHand  = $this->analysePlayerLineResults($firstPlayerCards);
       $secondPlayerHand = $this->analysePlayerLineResults($secondPlayerCards);
       
       $firstPlayerResult  = $this->getResult($firstPlayerHand);
       $secondPlayerResult = $this->getResult($secondPlayerHand);
       
       if($firstPlayerResult == 0 && $secondPlayerResult == 0){
           
           $firstPlayerHighCard  = App\Http\helpers\ResultCheck::grapHighCard($firstPlayerHand);
           $secondPlayerHighCard = App\Http\helpers\ResultCheck::grapHighCard($secondPlayerHand);
           
           if(App\Http\helpers\PlayingCard::compare($firstPlayerHighCard, $secondPlayerHighCard)){
               $this->result = $this->result . "Player A : has a card : ".$firstPlayerHighCard. " Weighter Than Player B : ".$secondPlayerHighCard."<br/>";
               $this->firstPlayerWinTimes++; 
           }else{
               $this->result = $this->result . "Player B : has a card : ".$secondPlayerHighCard." Weighter Than Player A : ".$firstPlayerHighCard."<br/>";
           }
               
       }else{
           
           if($firstPlayerResult < $secondPlayerResult)
                $this->firstPlayerWinTimes++;
           
           if($firstPlayerResult != 0){
               $this->result = $this->result . "Player A :".self::$ranks[$firstPlayerResult]."<br/>";
           }else{
               $firstPlayerHighCard  = App\Http\helpers\ResultCheck::grapHighCard($firstPlayerHand);
               $this->result = $this->result . "Player A High Card : ".$firstPlayerHighCard."<br/>";
           }
           
           if($secondPlayerResult != 0){
               $this->result = $this->result . "Player B :".self::$ranks[$secondPlayerResult];
           }else{
               $secondPlayerHighCard = App\Http\helpers\ResultCheck::grapHighCard($secondPlayerHand);
               $this->result = $this->result . "Player B High Card : ".$secondPlayerHighCard; 
           }
       }
       
    }
    
    public function analysePlayerLineResults($playerCards){
        $hand = new App\Http\helpers\PokerHand();
        $values = $playerCards;
        
        // Create an array of clubs.
        for ($i = 0; $i < 5; $i++) {
            // Add a card value of the same suit.
            $card  = trim($values[$i]);
            $value = trim($values[$i][0]);
            $suit  = trim($values[$i][1]);
            
            $hand->addCard($card , $suit, $value);
        }
        
        return $hand;
           
    }
    
    
        
    /*
     * Delete the poker hand records from DB
    */
    public function getResult($hand){
        
        if(App\Http\helpers\ResultCheck::isRoyalFlush($hand)){
            return 1;
        }else if(App\Http\helpers\ResultCheck::isStraightFlush($hand)){
            return 2;
        }else if(App\Http\helpers\ResultCheck::isFourOfAKind($hand)){
            return 3;
        }else if(App\Http\helpers\ResultCheck::isFullHouse($hand)){
            return 4;
        }else if(App\Http\helpers\ResultCheck::isFlush($hand)){
            return 5;
        }else if(App\Http\helpers\ResultCheck::isStraight($hand)){
            return 6;
        }else if(App\Http\helpers\ResultCheck::isThreeOfAKind($hand)){
            return 7;
        }else if(App\Http\helpers\ResultCheck::isTwoPair($hand)){
            return 8;
        }else if(App\Http\helpers\ResultCheck::isOnePair($hand)){
            return 9;
        }else {
            return 0;
        }

    }
}
