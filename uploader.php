<?php
if (isset($_POST['submit']) && $_POST['submit'] == 'Upload File' && isset($_FILES['fileToUpload'])) {
    unset($_POST['submit']);
    $file_upload_msg = "";
    $target_dir = "backgrounds/";
    $file_name = basename($_FILES["fileToUpload"]["name"]);
    $target_file = $target_dir . $file_name;
    $formatted_file_name = ucwords(strtolower(str_replace(["'"], "\'", str_replace(['_', '.'], ' ', trim(pathinfo($file_name, PATHINFO_FILENAME))))));

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO background_images (image_name, image_url) VALUES ('$formatted_file_name', '{$conn->real_escape_string($file_name)}')";

        if ($conn->query($sql) === TRUE) {
            $file_upload_msg = 'Background uploaded';
        } else {
            $file_upload_msg = 'Background upload failed';
            echo '<script>console.log("FILE Error SQL:",' . json_encode($sql) . ');</script>';
            echo '<script>console.log("FILE Error MSG:",' . json_encode($conn->error) . ');</script>';
        }
    } else {
        $file_upload_msg = "There was an error uploading your file. Please try again.";
    }
    unset($_FILES["fileToUpload"]);

}
?>