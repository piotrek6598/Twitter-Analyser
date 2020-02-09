<html>
<body>
<?php
$name = $_POST['name'];
$user = $_POST['user'];
$password = $_POST['password'];

if (empty($name)) {
	echo "Username wasn't entered";
	exit;
}

function form ($c) {
	if ($c == 1)
		return "";
	return "s";
}

function expandGroup($c){
	if ($c == 0)
		return "Between 00:00 and 05:59 user posted ";
	if ($c == 1)
		return "Between 06:00 and 11:59 user posted ";
	if ($c == 2)
		return "Between 12:00 and 17:59 user posted ";
	return "Between 18:00 and 23:59 user posted ";
}


$conn = oci_connect($user, $password);
if (!$conn) {
	$e = oci_error();
	echo "Connection to databse failed.({$e['message']})\n";
	exit;
}

$cursor = oci_new_cursor($conn);
$sql = "BEGIN profile_time_analysis(:name, :tweets, :hashtags, :cursor);END;";
$stmt = oci_parse($conn, $sql);

oci_bind_by_name($stmt, ":name", $name);
oci_bind_by_name($stmt, ":tweets", $tweets);
oci_bind_by_name($stmt, ":hashtags", $hashtags);
oci_bind_by_name($stmt, ":cursor", $cursor, -1, OCI_B_CURSOR);

oci_execute($stmt);
if (empty($tweets)){
	oci_free_statement($stmt);
	oci_free_statement($cursor);
	oci_close($conn);
	echo "User <b>" . $name . "</b> doesn't exist in database";
	exit;
}
oci_execute($cursor);

echo "User <b>" . $name . "</b> posted <b>" . $tweets .  "</b> tweet" . form($tweets) . ", used <b>". $hashtags . 
	"</b> hashtag" . form($hashtags) . "<br />\n";
while (($row = oci_fetch_array($cursor, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
	echo expandGroup($row['INTERVAL']) . " <b>" . $row['TWEETS'] . "</b> tweet" . form($row['TWEETS']) . "<br />\n";
}

oci_free_statement ($stmt);
oci_free_statement ($cursor);
oci_close ($conn);

?>
</body>
</html>
