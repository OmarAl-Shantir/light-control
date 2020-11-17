<?php
require("config.php");

$state=array(0=>"/?a=1", 1=>"/?a=0");

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, name, status, description, address FROM light";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
      $light[$row["id"]]=array($row["name"],$row["status"],$row["description"]);
      $device[$row["name"]]=array($row["id"],$row["address"]);
    }
} else {
    echo "0 results";
}

if (!empty($_POST)){
  $switch=key($_POST);
  $url=$device[$switch][1];
  $newStat=($light[$device[$switch][0]][1]==1?0:1);
  $url.=($newStat==1?$state[1]:$state[0]);
  //echo $url;
  $sql = "UPDATE light SET status=$newStat WHERE id=".$device[$switch][0];
  $sql2 = "INSERT INTO history (id_light, status) VALUES (".$device[$switch][0].", $newStat)";
  $conn->query($sql);
  $conn->query($sql2);
  //header("Location: ".$url); die();
  file_get_contents($url);
  header("Location: ".$_SERVER['PHP_SELF']);die();
  unset($_POST);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/light.css">
    <title>Light control</title>
  </head>
  <body>
    <div class="content">
      <table>
        <tbody>
            <?php
              foreach ($light as $key => $values) {
                $host = $device[$values[0]][1];
                $host = substr($host,7);
                $port = 80;
                $waitTimeoutInSeconds = 1;
                $fp = fsockopen($host,$port,$errCode,$errStr,$waitTimeoutInSeconds);
                echo "<tr>";
                echo "<td>".$values[2].":</td>";
                echo "<td>";
                if ($fp){
                  echo "<form class='checkbox' action='' method='post'>";
                }
                echo "<label class='switch'>";
                echo "<input type='submit' name='".$values[0]."' >";
                if ($fp){
                  echo "</form>";
                }
                echo "<span class='slider";
                if ($fp){
                  if ($values[1]=="1") {
                    echo " checked";
                  } else {
                    echo " unchecked";
                  }
                } else {
                  if ($values[1]=="1") {
                    echo " checked unactive";
                  } else {
                    echo " unchecked unactive";
                  }
                }
                echo "'><span class='slider_text'>".$values[2]."</span></span>";
                echo "<span class='isok";

                if ($fp){
                  echo " ok";
                } else {
                  echo " nook";
                }
                fclose($fp);
                echo "'></span>";
                echo "</label>";
                echo "</td>";
                echo "</tr>";
              }
              ?>
        </tbody>
      </table>
    </div>
  </body>
</html>
