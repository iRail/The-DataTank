<?php
/**
 * This file contains the logic to setup a new method through a webinterface.
 * @package The-Datatank/resources
 * @copyright (C) 2011 by iRail vzw/asbl
 * @license AGPLv3
 * @author Jan Vansteenlandt <jan@iRail.be>
 */
ini_set("include_path", "../");
include_once ("templates/TheDataTank/header.php");
?>
<form id="createMethod" class="createMethod"  method="post" action="">
	<div class="form_description">
		<h2>Create new method</h2>
		<p>
			Fill in the following fields to create a method!
		</p>
	</div>
	<ul >

		<li id="li_1" >
			<label class="description" for="module">
				Module
			</label>
			<div>
				<input id="module" name="module" class="element text medium" type="text" maxlength="255" value=""/>
			</div>
			<p class="guidelines" id="guide_1">
				<small>
					Enter a modulename, if the module doesn't exist it'll be created for you.
				</small>
			</p>
		</li>
		<li id="li_2" >
			<label class="description" for="method">
				Method
			</label>
			<div>
				<input id="method" name="method" class="element text medium" type="text" maxlength="255" value=""/>
			</div>
			<p class="guidelines" id="guide_2">
				<small>
					Name of the method. Make sure it doesn't exist already!
				</small>
			</p>
		</li>
		<li id="li_3" >
			<label class="description" for="reqparams">
				Required parameters
			</label>
			<div>
				<input id="reqparams" name="reqparams" class="element text medium" type="text" maxlength="255" value=""/>
			</div>
			<p class="guidelines" id="guide_3">
				<small>
					Enter the names of the parameters that are required to handle the request.
				</small>
			</p>
		</li>
		<li id="li_4" >
			<label class="description" for="allowedparams">
				Allowed parameters
			</label>
			<div>
				<textarea id="allowedparams" name="allowedparams" class="element textarea medium" value="">
				</textarea>
			</div>
			<p class="guidelines" id="guide_4">
				<small>
					Add all (including the required ones) parameters here along with the documentation of the parameters. i.e: param1,documentation about param1 ; param2,documentation about param2; param3, documentation about param3
				</small>
			</p>
		</li>
		<li id="li_5" >
			<label class="description" for="documentation">
				Documentation
			</label>
			<div>
				<textarea id="documentation" name="documentation" class="element textarea medium" value="">
				</textarea>
			</div>
			<p class="guidelines" id="guide_5">
				<small>
					Add documentation for your method.
				</small>
			</p>
		</li>
		<li id="li_6" >
			<label class="description" for="allowedformats">
				Allowed printmethods
			</label>
			<div>
				<input id="allowedformats" name="allowedformats" class="element text medium" type="text" maxlength="255" value=""/>
			</div>
			<p class="guidelines" id="guide_6">
				<small>
					Currently supported: xml,json,jsonp,php,html,kml. example xml;json;jsonp
				</small>
			</p>
		</li>

		<li class="buttons">
			<input type="hidden" name="form_id" value="219228" />

			<input id="saveForm" class="button_text" type="submit" name="submit" value="Create method" />
		</li>
	</ul>
</form>
<?php
include_once ("templates/TheDataTank/footer.php");
?>

