<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>The Data-Tank</title>
        <link rel="stylesheet" href="http://twitter.github.com/bootstrap/assets/css/bootstrap-1.1.1.min.css">  
        <link rel="stylesheet" href="main.css">
        <script type="text/javascript" src="underscore.js"></script>
        <script type="text/javascript" src="backbone.js"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
        <script type="text/javascript" src="jquery.tablesorter.min.js"></script>
        <script type="text/javascript" src="admin.js"></script>
    </head>
    <body>
        <!-- Materhead -->
        <div id="masterhead" class="container">
            <div class="row">
                <div class="span10 columns"><a href="#"><img src="logo.png"/></a></div>
                <div class="span6 columns">
                    <div id="menu">
                      <a href="#">Home</a>
                      <a href="#">About</a>
                      <a href="#">Documentation</a>
                      <a href="#">Admin</a>
                    </ul>               
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div id="main"><div class="container">
                {{ phpinfo() }}
            <div class="topbar-wrapper"><div class="topbar">
                  <div class="fill">
                    <div class="container">
                      <ul>
                          <li class="active"><a href="#admin-admin-page">Admin</a></li>
                          <li><a href="#admin-module-page">Modules</a></li>
                          <li><a href="#admin-resource-page">Resources</a></li>
                          <li><a href="#admin-statistic-page">Statistics</a></li>
                          <li><a href="#admin-profile-page">Profile</a></li>
                      </ul>
                      <ul class="nav secondary-nav">
                        <li><a href="#admin-logout-page">Logout</a></li>
                      </ul>
                    </div>
                  </div><!-- /fill -->
            </div></div>
            
            <div id="admin-admin-page">
                <h1>Admin</h1>
                <div class="row">
                    <div class="span7 columns">
                        <table>
                            <thead>
                                <tr>
                                    <th>Open Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="#">Modules</a></td>
                                </tr>
                                <tr>
                                    <td><a href="#">Resources</a></td>
                                </tr>
                                <tr>
                                    <td><a href="#">CKAN Repositories</a></td>
                                </tr>
                            </tbody>
                        </table>
                        <table>
                            <thead>
                                <tr>
                                    <th>Other</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="#">Statistics</a></td>
                                </tr>
                                <tr>
                                    <td><a href="#">Profile</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="span5 columns offset1">
                        <table>
                            <thead>
                                <tr>
                                    <th>Recent Changes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Added resource: <a href="#">Dag 1</a></td>
                                </tr>
                                <tr>
                                    <td>Added resource: <a href="#">Dag 2</a></td>
                                </tr>
                                <tr>
                                    <td>Added resource: <a href="#">Dag 3</a></td>
                                </tr>
                                <tr>
                                    <td>Added resource: <a href="#">Dag 4</a></td>
                                </tr>
                                <tr>
                                    <td>Added resource: <a href="#">Dag 5</a></td>
                                </tr>
                                <tr>
                                    <td>Added resource: <a href="#">Dag 6</a></td>
                                </tr>
                                <tr>
                                    <td>Added resource: <a href="#">Dag 7</a></td>
                                </tr>
                                <tr>
                                    <td>Added resource: <a href="#">Dag 8</a></td>
                                </tr>
                                <tr>
                                    <td>Added resource: <a href="#">Dag 9</a></td>
                                </tr>
                                <tr>
                                    <td>Added resource: <a href="#">Dag 10</a></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

            <div id="admin-module-page">
                <h1>Modules</h1>
                <table id="admin-modules" class="zebra-striped">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th class="yellow">#</th>
                            <th>Name</th>
                            <th>Publication Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>1</td>
                            <td>Gentse Feesten</td>
                            <td>24, July 1988</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>2</td>
                            <td>Pukkel Pop</td>
                            <td>3, August 1990</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>3</td>
                            <td>Gras Rock</td>
                            <td>12, September 2004</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>4</td>
                            <td>Pukkel Pop</td>
                            <td>3, August 1990</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>5</td>
                            <td>Gras Rock</td>
                            <td>12, October 2004</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>6</td>
                            <td>Gentse Feesten</td>
                            <td>24, July 1988</td>
                        </tr>
                    </tbody>
                </table>

                <form class="form-stacked">
                    <fieldset>
                        <legend>Add module</legend>
                        <div class="clearfix">
                            <label>Module name</label>
                            <div class="input">
                                <input type="text" class="xlarge">
                            </div>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn primary">Save</button>&nbsp;
                            <button type="reset" class="btn">Cancel</button>
                        </div>
                    </fieldset>
                </form>
            </div>

            <div id="admin-resource-page">
                <h1>Resources</h1>
                <table id="admin-resources" class="zebra-striped">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th>#</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Module</th>
                            <th>Publication Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>1</td>
                            <td>Dag 1</td>
                            <td>CSV</td>
                            <td>Gentse Feesten</td>
                            <td>24, July 1988</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>2</td>
                            <td>Dag 2</td>
                            <td>CSV</td>
                            <td>Gentse Feesten</td>
                            <td>24, July 1988</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>3</td>
                            <td>Dag 3</td>
                            <td>CSV</td>
                            <td>Gentse Feesten</td>
                            <td>24, July 1988</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>4</td>
                            <td>Dag 4</td>
                            <td>CSV</td>
                            <td>Gentse Feesten</td>
                            <td>24, July 1988</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>5</td>
                            <td>Dag 5</td>
                            <td>CSV</td>
                            <td>Gentse Feesten</td>
                            <td>24, July 1988</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>6</td>
                            <td>Dag 6</td>
                            <td>CSV</td>
                            <td>Gentse Feesten</td>
                            <td>24, July 1988</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>7</td>
                            <td>Dag 7</td>
                            <td>CSV</td>
                            <td>Gentse Feesten</td>
                            <td>24, July 1988</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>8</td>
                            <td>Dag 8</td>
                            <td>CSV</td>
                            <td>Gentse Feesten</td>
                            <td>24, July 1988</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>9</td>
                            <td>Dag 9</td>
                            <td>CSV</td>
                            <td>Gentse Feesten</td>
                            <td>24, July 1988</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"/></td>
                            <td>10</td>
                            <td>Dag 10</td>
                            <td>CSV</td>
                            <td>Gentse Feesten</td>
                            <td>24, July 1988</td>
                        </tr>
                    </tbody>
                </table>

                <form class="form-stacked">
                    <fieldset>
                        <legend>Add resource</legend>
                        <div class="clearfix">
                            <label>Resource name</label>
                            <div class="input">
                                <input type="text" class="xlarge">
                            </div>
                        </div>
                        <div id="admin-upload" class="clearfix">
                            <label>Your file</label>
                            <div class="input">
                                <button class="btn large" id="admin-upload-computer"><img src="computer16.png"/> Computer</button>
                                <button class="btn large" id="admin-upload-link"><img src="link16.png"/> Link</button>
                                <span class="help-block">
                                    <strong>Note:</strong> or drap and drop a file onto this page.
                                </span>
                            </div>
                        </div>
                        <div id="admin-file" class="clearfix hidden">
                            <label>Your file</label>
                            <div class="input-prepend">
                                <span class="add-on"><a id="admin-remove-upload" class="delete_input" href="#"><img src="trash.gif"/></a></span>
                                <input class="xlarge disabled" id="disabledInput" name="disabledInput" size="30" type="text" placeholder="Gentse Feesten Dag1.csv" disabled="">
                            </div>                        
                        </div>
                        <div class="clearfix">
                            <label>Module</label>
                            <div class="input">
                                <select class="xlarge"></select>
                            </div>
                        </div>
                        <div class="clearfix">
                            <label>Documentation</label>
                            <div class="input">
                                <textarea class="xxlarge" rows="10"></textarea>
                            </div>
                        </div>
                        <div class="clearfix">
                            <label>Publish to CKAN</label>
                            <div class="input">
                                <input type="checkbox" class="xlarge">
                            </div>
                        </div>
                        <div class="actions">
                            <button type="submit" class="btn primary">Save</button>&nbsp;
                            <button type="reset" class="btn">Cancel</button>
                        </div>
                </form>
            </div>
        </div>
    </body>
</html>
