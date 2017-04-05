<!DOCTYPE html>
<html>
<head>
    <title>Top Color</title>
</head>
<body>

<style>
    * {
        font-family: Arial, Helvetica, sans-serif;
    }

    .button {
        background-color: #555; /* Green */
        border: none;
        color: white;
        padding: 8px 32px;
        text-align: center;
        display: inline-block;
        font-size: 16px;
        margin: 4px 32px;
        cursor: pointer;
        font-weight:bold;
    }

    div {
        padding-bottom: 15px;
    }

    input#chk {
        vertical-align:middle;
    }
</style>

<h3>
    <u>Top 5 Dominating Color</u>
</h3>
<form action="UploadImage.php" method="post" enctype="multipart/form-data">
    <div>
        Add Image:
        <input type="file" name="img">
    </div>

    <div>
        Is background White? <input type="checkbox" name="whiteBG" id="chk">
    </div>

    <div>
        <input type="submit" value="Upload" name="submit" class="button">
    </div>
</form>
</body>
</html>
