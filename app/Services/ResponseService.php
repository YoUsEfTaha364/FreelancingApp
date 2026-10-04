<?php

  namespace App\Services;



   class ResponseService
  {

      public static function Response($data=[],$status=200,$message=""){

        $d=[
            "message"=>$message,
            "data"=>$data
        ];

         return response()->json($d,$status);

      }
  }



  ?>