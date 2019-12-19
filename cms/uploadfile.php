<?PHP
include("engine.php");
session_start();
if ( !isset($_SESSION['_session_user_name']) ){
    return;
}

if(!empty($_FILES['file']))
{
    //$path = "uploads/";
    //$path = $path . basename( $_FILES['uploaded_file']['name']);
    //print_r( $_FILES['file'] );
    //print_r( $_POST );
    
    upload2sqlite( $_FILES['file'], $_POST['selectedfolder'] );
    /*
    if(move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $path)) {
        echo "The file ".  basename( $_FILES['uploaded_file']['name']). 
        " has been uploaded";
    } else{
        echo "There was an error uploading the file, please try again!";
    }
    */
}
?>