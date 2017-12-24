<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Uncaught Exception</title>
<style type="text/css">
body {
  color:#666;
}
h1 {
  color:#F60;
  border-bottom:#F60 double 3px;
  padding:5px 0;
}
p strong {
  margin-right:10px;
}
.me_trace {
  color:#039;
  background-color:#FEF8C2;
  border:#F60 dashed 1px;
  padding:10px;
  font-family: "Courier New",Courier,monospace;
}
.me_index {margin-right:5px;}
</style>
</head>
<body>
  <h1>Uncaught Exception</h1>
  <p><strong>Code:</strong><?php echo $ex->getCode();?></p>
  <p><strong>Name:</strong><?php echo get_class($ex);?></p>
  <p><strong>Message:</strong><?php echo $ex->getMessage();?></p>
  <p><strong>Position:</strong><?php echo $ex->getFile(), '#', $ex->getLine();?></p>
  <p><strong>Trace:</strong></p>
  <pre class="me_trace"><?php echo $ex->getTraceAsString();?></pre>
</body>
</html>
