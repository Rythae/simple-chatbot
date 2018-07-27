<?php

	if(!defined('DB_USER')){
	  require "config.php";		
	  try {
	      $conn = new PDO("mysql:host=". DB_HOST. ";dbname=". DB_DATABASE , DB_USER, DB_PASSWORD);
	  } catch (PDOException $pe) {
	      die("Could not connect to the database " . DB_DATABASE . ": " . $pe->getMessage());
	  }
	}
	//Fetch User Details 
	try {
	    $query = "SELECT * FROM interns_data WHERE username ='Rita12'";
	    $result = $conn->query($query);
	    $result2 = $result->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $e){
	    throw $e;
	}
	$username = $result2['username'];
	$fullName = $result2['name'];

	//Fetch Secret Word
	try{
	    $queryKey =  "SELECT * FROM secret_word LIMIT 1";
	    $result   =  $conn->query($queryKey);
	    $result2  =  $result->fetch(PDO::FETCH_ASSOC);
	    $secret_word =  $result2['secret_word'];
	}catch (PDOException $e){
	    throw $e;
	}
	$secret_word =  $result2['secret_word'];

?>

<?php
//check if server method = post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	//Get the input text from the user and store in a new vaariable called "question"	 

	$question = $_POST['question'];

	$question = preg_replace('([\s]+)', ' ', trim($question)); 
	$question = preg_replace("([?.])", "", $question); 
	// check if question is "aboutbot"
	if (strtolower(trim($question)) === "aboutbot") {
			  echo json_encode([
			     'status' => 1,
       			 'answer' => "This bot is in version 1.0. built by Rita"
     		 ]);

		return;
	};
		
	 //Check if user want to train the bot or ask a normal question
	$checking = stripos($question, "train:");
	
	if ($checking === false) { //then user is asking a question

		//remove extra white space, ? and . from question
	    $question = preg_replace('([\s]+)', ' ', trim($question)); 
	    $question = preg_replace("([?.])", "", $question); 

	     //check database for the question and return the answer
	    $question = $question;
        $sql = 'SELECT * FROM chatbot WHERE question = "'. $question . '"';
        $q = $GLOBALS['conn']->query($sql);
        $q->setFetchMode(PDO::FETCH_ASSOC);
        $data = $q->fetchAll();
        if(empty($data)){//That means your answer was not found in the database
            echo json_encode([
        		'status' => 1,
       			 'answer' => "Am not sure i understand that but you can train me by typing--> train: your question # answer # password."
     		 ]);
          return;

        }else {
            $random = array_rand($data);
            $answer = $data[$random]['answer'];

            echo json_encode([
        		'status' => 1,
       			 'answer' => $answer, //return one of the the answers to client
     		 ]);
           return;
        }

	}else{ // in training mode
		//train the chatbot to be more smarter 
		//remove extra white space, ? and . from question
		$userText = preg_replace('([\s]+)', ' ', trim($question)); 
	    $userText = preg_replace("([?.])", "", $userText); 
	      //get the question and answer by removing the 'train'
		$userText = substr($userText, 6);
		//get the index of the user question
     	$userText = explode("#", $userText);

     	$user_question = trim($userText[0]);
		if(count($userText) == 1){//then the user only enter question and did'nt enter answer and password
			echo json_encode([
				'status' => 1,
				'answer' => "You have entered an invalid format.You can enter the correct format by typing-->train: question # answer # password"
			]);
			return;
		};

		 //get the index of the user answer
	    $user_answer = trim($userText[1]);    
        if(count($userText) < 3){ //the user only enter question and answer without password
	        echo json_encode([
	          'status' => 1,
	          'answer' => "Please enter training password to train me. The password is: password"
	        ]);
        	return;
        };

         //get the index of the user password
	    $user_password = trim($userText[2]);

        //verify if training password is correct
        define('PASSWORD', 'password'); //this is a constant variable
        if($user_password !== PASSWORD){ //the password is incorrect
	        echo json_encode([
	          'status' => 1,
	          'answer' => "Your password is not correct, you cannot train me."
	        ]);
     		return;
    	};

	    //check database if answer exist already
   		$user_answer = "$user_answer"; //return things that have the question
	    $sql = "SELECT * FROM chatbot WHERE answer LIKE :user_answer";
	    $stmt = $conn->prepare($sql);
	    $stmt->bindParam(':user_answer', $user_answer);
	    $stmt->execute();
	    $stmt->setFetchMode(PDO::FETCH_ASSOC);

	 	$rows = $stmt->fetchAll();
	    if(empty($rows)){ // then it means the database could not fetch any existing question and answer, so we can insert the query.          
		    $sql = "INSERT INTO chatbot (question, answer) VALUES (:question, :answer)";  //insert into database
		    $stmt = $conn->prepare($sql);
		    $stmt->bindParam(':question', $user_question);
		    $stmt->bindParam(':answer', $user_answer);
		    $stmt->execute();
		    $stmt->setFetchMode(PDO::FETCH_ASSOC);
		    
		    echo json_encode([
		    	'status' => 1,
		        'answer' =>  "Awesome! Learning is sweet! Thank you for teaching me that buddy, and for making me more smarter too! "
		      ]);			
     		return;
     	
     	}else{//then it means that the question is already in the database and no need to insert it again

     		 echo json_encode([
		    	'status' => 1,
		        'answer' => "Sorry! Answer already exist. You can train me again with same question but with an alternative answer. You can as well train me again with a new question and a new answer."
		      ]);
			return;		
     	};
    	return;
	};

}else{ 

?>

<!DOCTYPE HTML>
<html>
	<head>
		<!--This site is a sample project for HNG internship 4.0 for stage1. by Rythae -->
		<meta charset="UTF-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    	<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<!-- <script type="text/javascript" src="https://code.jquery.com/jquery-1.9.1.min.js"></script> -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<title>My Profile</title>

		<style>

			 html, body {
			  width: auto !important;
			  overflow-x: hidden !important;
			}
			
			* {
				margin: 0;
				padding: 0;
				font-family: tahoma, sans-serif;
				box-sizing: border-box;
			}

			body {
				padding:0;
				margin:0;
				font-family: 'Roboto', sans-serif;
				font-size: 100%;
				background: #1ddced;
			}

			.chatbox {
				width: 500px;
				min-width: 390px;
				height: 600px;
				background: #fff;
				padding: 25px;
				margin: 20px auto;
				box-shadow: 0 3px #ccc;
			}

			.chatlogs {
				padding: 10px;
				width: 100%;
				height: 450px;
				background: #eee;
				overflow-x: hidden;
				overflow-y: scroll;
			}

			.chatlogs::-webkit-scrollbar {
				border-radius: 5px;
				background: rgba(0,0,0,0.1);
			}

			.chatlogs::-webkit-scrollbar-thumb {
				width: 10px;
			}

			.chat {
				display: flex;
				flex-flow: row wrap;
				align-items: flex-start;
				margin-bottom: 10px;
			}

			.chat .user-photo {
				width: 60px;
				height: 60px;
				background: #ccc;
				border-radius: 50%;
			}

			.chat .chat-message {
				width: 80%;
				padding: 15px;
				margin: 5px 10px 0;
				border-radius: 10px;
				color: #fff;
				font-size: 20px;
			}

			.friend .chat-message {
				background: #1adda4;
			}

			.self .chat-message {
				background: #1ddced;
				order: -1;
			}

			.chat-form {
				margin-top: 20px;
				display: flex;
				align-items: flex-start;
			}

			.chat-form textarea {
				background: #fbfbfb;
				width: 75%;
				height: 50px;
				border: 2px solid #eee;
				border-radius: 3px;
				resize: none;
				padding: 10px;
				font-size: 18px;
				color: #333;
			}

			.chat-form textarea:focus {
				background: #fff;
			}

			.chat-form textarea::-webkit-scrollbar {
				border-radius: 5px;
				background: rgba(0,0,0,0.1);
			}

			.chat-form textarea::-webkit-scrollbar-thumb {
				width: 10px;
			}

			.chat-form button {
				background: #1ddced;
				padding: 5px 15px;
				font-size: 30px;
				color: #fff;
				border: none;
				margin: 0 10px;
				border-radius: 3px;
				box-shadow: 0 3px 0 #0eb2c1;
				cursor: pointer;

				-webkit-transition: background .2s ease;
				-moz-transition: background .2s ease;
				-o-transition: background .2s ease;
			}

			.chat-form button:hover {
				background: #13c8d9;
			}

			.container {
				text-align: center;
			}
			.card {
			  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
			  width: 18rem;
			  margin: auto;
			  text-align: center;
			  font-family: arial;
			}

			.cover {
				background-color: steelblue;
				padding: 20px 10px;
				color: #FFFFFF;
				width: 200px;
				height: 200px;

			}

			.title {
			  color: grey;
			  font-size: 18px;
			  text-align: center;
			  padding-top: 40px;
			}

			h5 {
				font-size: 18px;
					padding-top: 0;
			}

			p {
				font-size: 15px;
			}


			a {
			  text-decoration: none;
			  font-size: 22px;
			  color: black;
			}

			 a:hover {
			  opacity: 0.7;
			}

			@media screen and (max-width: 760px){
				.chatlogs {
			    padding: 10px;
			    width: 70%;
			    height: 450px;
			    background: #eee;
			    overflow-x: hidden;
			    overflow-y: scroll;
				}
			}

			@media screen and (max-width: 400px){
				.chatlogs {
			    padding: 10px;
			    width: 75%;
			    height: 450px;
			    background: #eee;
			    overflow-x: hidden;
			    overflow-y: scroll;
				}
			}
	</style>
</head>
<body>
	
	<div class="container-fluid">
	<h2 class="title">My Profile Card</h2>

	<div class="container">
	<div class="card">
	  <img class="card-img-top" src="" alt="Rita" style="width:200px; height: 200px">
	    <div class="cover">
	    <h5> <?php echo $fullName; ?> </h5>
	    <p>PHP beginner, Javascript intermediate</p>
	    <a href="#" class="btn btn-info">Meet Me!</a>
	  <div style="margin: 24px 0; padding-bottom: 20px">
	    <a href="#"><i class="fa fa-twitter"></i></a>  
	    <a href="#"><i class="fa fa-linkedin"></i></a>  
	    <a href="#"><i class="fa fa-facebook"></i></a> 
	    <a href="#"><i class="fa fa-github"></i></a>
	 </div>
	</div>
  </div>
</div>
	
	<div class="chatbox">		
		<div class="chatlogs" id="chatlogs">
			<div class="chat friend"> 
				<div class="user-photo"></div>
				<p class="chat-message">Hello, I am Kel!</p>
			</div>
			<div class="chat friend"> 
				<div class="user-photo"></div>
				<p class="chat-message">You can ask me questions and also train me to understand stuffs i dont know</p>
			</div>
			<div class="chat friend"> 
				<div class="user-photo"></div>
				<p class="chat-message">To train me, please type<br><code>train: your question # answer # password</code></p>
			</div>
			<div id="view-chat">
				
			</div>
			<div class="chat-form">
				<textarea id="question"  name="question" placeholder="Ask a question.."></textarea>
				<button id ="send">Send</button>
			</div>
		</div>
	</div>
	</div>
	
	<script type="text/javascript"> 

		$(document).ready(function(){  
			var showDisplay = $("#view-chat"); 
		    $("#send").click(function(event){ 
				event.preventDefault();
				var newMessage = $("#question");
				var question = newMessage.val(); 
				var empty_message = "Please enter a question";
				if(question.trim() == ''){
					showDisplay.append(					 	
					 	'<div class="chat friend">'+'<div class="user-photo"></div>'
					 	+'<p class="chat-message">'+empty_message+'</p>'+'</div>'
					);

		       		$("#chatlogs").scrollTop($("#chatlogs")[0].scrollHeight);
				}else{
			        showDisplay.append(
			        	'<div class="chat self">'+'<div class="user-photo"></div>'
			        	+'<p class="chat-message">'+question+'</p>'+'</div>'
			        );

			        $("#chatlogs").scrollTop($("#chatlogs")[0].scrollHeight);
		       };

					//after appending user question, send it to server for processing
				$.ajax({
						url: "Rita12.php",
						dataType : "json",
						type: "POST",
						data: {question: question},
						success: function(data) {
							if(data.status == 1){
								showDisplay.append(					 	
								 	'<div class="chat friend">'+'<div class="user-photo"></div>'
								 	+'<p class="chat-message">'+data.answer+'</p>'+'</div>'
								);

		       					$("#chatlogs").scrollTop($("#chatlogs")[0].scrollHeight);

							} 
			       		},

						error: function(error){
							console.log(error);
						}
				});
				newMessage.val(""); 				
			});
		}); 

	</script>
</body>
</html>
<?php } ?>
