<!DOCTYPE html>
<html>
<head>
  <title>Upload your files</title>
</head>
<body>
  <form enctype="multipart/form-data" action="upload.php" method="POST">
    <p>Upload your file</p>
    <input type="file" name="file"></input><br />
    <input type="submit" value="Upload"></input>
  </form>
</body>
</html>
<?PHP
include("engine.php");

if(!empty($_FILES['file']))
{
    //$path = "uploads/";
    //$path = $path . basename( $_FILES['uploaded_file']['name']);
    print_r( $_FILES['file'] );
    upload2sqlite( $_FILES['file'] );
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