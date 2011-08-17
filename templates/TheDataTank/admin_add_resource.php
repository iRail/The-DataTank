<?php

echo '
<script>
	$(function() {
		$( "#tabs" ).tabs();
	});
</script>
<h2>Add Resource</h2>
<div id="tabs">
	<ul>
		<li><a href="#tabs-1">CSV</a></li>
		<li><a href="#tabs-2">Escell</a></li>
		<li><a href="#tabs-3">DB</a></li>
	</ul>
	<div id="tabs-1">csv</div>
	<div id="tabs-2">excell</div>
	<div id="tabs-3">db</div>
</div>

';

?>
