<?php

namespace App\Transformers;


/**
*  Class Json is transformers from raw data to json view
*/
class Json
{
	public static function response($data = null, $page = null,  $additional=null, $message = null, $status = true)
    {	
        if ($message==null) {
            $message = 'success';
        }
        if ($data==null) {
            $data = [];
        }
        $result['meta']['status'] = $status;
	    $result['meta']['message'] = $message;
	    $result['meta']['code'] = 200;
    	if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
    		$result['pagination']['total'] = $data->total();
            $result['pagination']['count'] = $data->perPage();
            $result['pagination']['current'] =$data->currentpage();
            $result['pagination']['last']=$data->lastPage();
            $result['pagination']['next']=$data->nextPageUrl();
            $result['pagination']['prev']=$data->previousPageUrl();
    		$result['data'] = $data->all();
    	} else {
    		$result['data'] = $data;
    	}
        if ($additional!=null) {
            foreach ($additional as $add) {
                $result['meta'][$add['name']] = $add['data'];
            }
        }

        if($page) {
            $result['self'] = $page->fullUrl();
        }
    	// $result['code'] = 200;
	    $code = 200;
	    return response()->json($result, $code);
    }

    public static function exception($message = null,$error = null, $status = false, $code=200)
    {	
        if ($message==null) {
            $message = __('message.error');
        }
        $result['data'] = [];
        $result['meta']['status'] = $status;
	    $result['meta']['message'] = $message;
	    $result['meta']['code'] = $code;
        if ($error instanceof \ErrorException) {    
            $result['error']['message'] = $error->getMessage();
            $result['error']['file'] = $error->getFile();
            $result['error']['line'] = $error->getLine();
        } else {
    	   $result['error'] = $error; 
        }
	    return response()->json($result, 200);
    }
    
}


