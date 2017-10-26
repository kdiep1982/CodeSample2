<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index(Request $request){
    	$user_name=$request->json()->get("Username");
    	$is_active =$request->json()->get("IsActive");
    	$password=$request->json()->get("Password");
    	$article_name=$request->json()->get("ArticleName");
    	$body=$request->json()->get("Body");
    	$categories=$request->json()->get("Categories");
    	$external_id=$request->json()->get("ExternalID");
    	 $keywords=$request->json()->get("Keywords");
    	$language=$request->json()->get("Language");
    	$name=$request->json()->get("Name");
    	$status=$request->json()->get("Status");
    	$title=$request->json()->get("Title");
    	$version_number=$request->json()->get("VersionNumber");
    	$description=$request->json()->get("Description");  
    	$tmp=array_filter(explode(",",$categories));
    	$string="";
    	
    	//validate user to make sure it's a valid user
    	$validate=app('db')->select("SELECT count(id) as id from wp_users where user_login='$user_name' and  user_pass='$password'");
    	
    	if($validate[0]->id=="1") {
    	
    		$results = app('db')->select("SELECT count(id) as id FROM wp_support_article_publication where external_id='".$external_id."'");
    		
    		
	    	  $results = app('db')->select("SELECT count(id) as id FROM wp_support_article_publication where external_id='".$external_id."'");
	     	if( $results[0]->id =="0") {
	    		
	     		$result=app('db')->insert("INSERT INTO `wp_support_article_publication` (`article_name`, `body`, `categories`, `external_id`, `keywords`, `language`, `name`, `status`, `title`, `version_number`, `description`, `created_date`)
	     				VALUES ('".$article_name."','".$body."','".$categories."','".$external_id."','".$keywords."','".$language."','".$name."','".$is_active."','".$title."','".$version_number."','".$description."','".date('Y-m-d')."')");
	     		
	    	}
	    	else{
	    		$orignal=app('db')->select("SELECT `id`,`article_name`, `body`, `categories`, `external_id`, `keywords`, `language`, `name`, `status`, `title`, 
	    				`version_number`, `description`, `created_date` FROM `wp_support_article_publication` where external_id='$external_id'");
	    		
	    		$result=app('db')->update("UPDATE `wp_support_article_publication` SET `article_name`='$article_name',`body`='$body',`categories`='$categories',`keywords`='$keywords',`language`='$language',
	    				`name`='$name',`status`='$is_active',`title`='$title',`version_number`='$version_number',`description`='$description' WHERE `external_id`='".$external_id."'");
	    		
	    		if($orignal[0]->article_name !=$article_name) {
	    			$update=app('db')->insert("INSERT INTO `wp_dlink_logs`(`object_name`, `field_name`, `record_id`, `user_name`, `modified_date`, `old_value`, `new_value`) VALUES ('api','article_name','".$orignal[0]->id."',
	    				'$user_name','".date('Y-m-d H:i:s')."','".$orignal[0]->article_name."','$article_name')");
	    		}
	    	} 
	    	
	    	$eid = app('db')->select("SELECT id as id FROM wp_support_article_publication where `external_id`='".$external_id."'");
	    	$result=app('db')->select("select count(id) as id from wp_support_article_publication_category where external_id='".$external_id."'"); 
	    	
	    	 if($result[0]->id=="0") {
	    		for($i=0; $i<count($tmp); $i++) {
	    			
	    			$temp=explode(":",$tmp[$i]);

	    				$result=app('db')->insert("INSERT INTO `wp_support_article_publication_category`(`article_name`, `support_article_publication_id`,`external_id`, `category_type`,
	    				`category`, `created_date`) VALUES ('$article_name','".$eid[0]->id."','$external_id', '$temp[0]','$temp[1]','".date('Y-m-d')."')");
	    		
	    		}
	    	
	    	} 
	    	
	    	 else{
	    		$result=app('db')->delete("DELETE FROM `wp_support_article_publication_category` where external_id='$external_id'");
	    		for($i=0; $i<count($tmp); $i++) {
	    			$temp=explode(":",$tmp[$i]);
	    		
	    			$result=app('db')->insert("INSERT INTO `wp_support_article_publication_category`(`article_name`, `support_article_publication_id`,`external_id`, `category_type`,
	    					`category`, `created_date`) VALUES ('$article_name','".$eid[0]->id."','$external_id', '$temp[0]','$temp[1]','".date('Y-m-d')."')");
	    		
	    		}
	    	}
	    	
	    	$result=app('db')->select("select count(id) as id from wp_support_article_publication_keyword where external_id='$external_id'");
	     	$tmp_keywords=array_filter(explode(",",$keywords));
	     	
	     	if($result[0]->id=="0") {
		    	 foreach($tmp_keywords as $key) {
		    		$result=app('db')->insert("INSERT INTO `wp_support_article_publication_keyword`(`article_name`, `support_article_publication_id`,`external_id`, `keywords`, `created_date`) VALUES ('$article_name','".$eid[0]->id."','$external_id','$key','".date('Y-m-d')."')");
		    		
		    	} 
	     	}
	     	else{
	     		$result=app('db')->delete("DELETE FROM `wp_support_article_publication_keyword`");
	     		foreach($tmp_keywords as $key) {
	     			$result=app('db')->insert("INSERT INTO `wp_support_article_publication_keyword`(`article_name`, `support_article_publication_id`,`external_id`,  `keywords`, `created_date`) VALUES ('$article_name','".$eid[0]->id."','$external_id','$key','".date('Y-m-d')."')");
	     			 
	     		}
	     	} 
	     	$today = date("Y-m-d H:i:s"); 
	     	
	     	 $result=app('db')->insert("INSERT INTO `wp_posts`(`post_author`, `post_date`, `post_content`, `post_title`,
	     			`post_status`, `post_name`, `post_type`) VALUES('1','$today','$body','$title',
	     			'publish','$title','page')"); 
	     	 
	     	 $lastID=app('db')->select("Select id from wp_posts where post_title = '$title'");
	     	
	     	 $update=app('db')->update("UPDATE `wp_posts` SET guid='http://10.6.8.161/?page_id=".$lastID[0]->id."' where id='".$lastID[0]->id."'");
	    //return "done";
	     	return response()->json(["Code"=>"Success"]); 
	    }
	    
	    else {
	    	return response()->json(["Error"=>"Invalide Username and/or Password"]);
	    }
	    
	    //create a page for the faq
	   
	    
    }
    
}
