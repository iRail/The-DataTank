
<script>
	$(function() {
		$( "#tabs" ).tabs();
	});
</script>
<h2>Add Resource</h2>
<div id="tabs">
	<ul class>
		<li><a href="#tabs-1">CSV</a></li>
		<li><a href="#tabs-3">DB</a></li>
	</ul>
    <div id="tabs-1">
        <form class="admin">
            <div class="form_row">
                <label class="admin">Name</label>
                <input type="text" />
            </div>
            <div class="form_row">
                <label class="admin">File</label>
                <input type="file" />
            </div>
            <div class="form_row">
                <label class="admin">Module</label>
                <select name="module">
                    <option value="volvo">Volvo</option>
                    <option value="saab">Saab</option>
                    <option value="fiat">Fiat</option>
                    <option value="audi">Audi</option>
                </select>
            </div>
            <div class="form_row">
                <label class="admin">Documentation</label>
                <textarea cols="65" rows="10"></textarea>
            </div>
            <div><input type="submit" value="Save" /></div>
            <input type="hidden" name="resource_type" value="csv" />
        </form>
    </div>
	<div id="tabs-3">db</div>
</div>
