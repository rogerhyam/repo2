<?php

require_once( '../../../config.php' );
require_once( '../tools_config.php' );

$include_css[] = "/tools/linked_data/linked_data.css";
$include_scripts[] = "/tools/linked_data/linked_data.js";

require_once( '../../inc/header.php' );

?>

<div class="repo-doc-page">
    <div id="tabs" >
        <ul>
            <li><a href="#tabs-import" id="tabs-validate-button">1. Import</a></li>
            <li><a href="#tabs-validate" id="tabs-validate-button">2. Validate</a></li>
            <li><a href="#tabs-response" id="tabs-response-button">3. Response</a></li>
        </ul>

        <div id="tabs-import">
            <form id="repo-import-form">
                <table id="upload-table">
                    <tr>
                        <td colspan="3">
                            <h3>Import Spreadsheet</h3>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <p>This is a three stage process. You cut'n'paste a block of cells from your spreadsheet into the box below. The data is validated and then imported. The response contains reference numbers that can be cut'n'paste back into your spreadsheet.</p>
                        </td>
                    </tr>
                    <tr>
	                    <th>Item Type:</th>
	                    <td colspan="2" ><select
	                        size="1"
	                        name="item_type"
	                        id="item_type"
	                        class="" 
                         />
                            <option>Silica Gel Sample</option>
                            <option>DNA Sample</option>
                         </select>
                         <a href="#">Template for silica gel samples.xls</a>
                         </td>
	                    <td class="help-cell">
 	                        <a href="#" class="repo-context-help" >?</a>
 	                        <div class="repo-help-dialogue" title="Title">
                               <p>
                                   You need to pick the kind of data you are importing...
                               </p>
                             </div>
 	                    </td>
	                </tr>
                    <tr>
	                    <th>Spreadsheet Data:</th>
	                    <td colspan="2" ><textarea
	                        rows="20"
	                        name="spreadsheet_data"
	                        id="spreadsheet_data"
	                        class="" 
                         /></textarea>
                         </td>
	                    <td class="help-cell">
 	                        <a href="#" class="repo-context-help" >?</a>
 	                        <div class="repo-help-dialogue" title="Title">
                               <p>
                                   Put your stuff in here...
                               </p>
                             </div>
 	                    </td>
	                </tr>
	                 <tr>
   	                    <td colspan="3" class="commit-cell"><button id="repo-validate-now-button" />Validate Now</button></td>
   	                    <td>&nbsp;</td>
   	                </tr>
                </table>
                
                
            </form>
        </div>
        <div id="tabs-validate">
             <table id="upload-table">
                    <tr>
                        <td colspan="3">
                            <h3>Validation Results</h3>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <p>Below are the results of validating your data.</p>
                        </td>
                    </tr>
                    <tr>
	                    <th>Results:</th>
	                    <td colspan="2" ><textarea
	                        rows="20"
	                        name="spreadsheet_results"
	                        id="spreadsheet_results"
	                        class=""
                         /></textarea>
                         </td>
	                    <td class="help-cell">
 	                        <a href="#" class="repo-context-help" >?</a>
 	                        <div class="repo-help-dialogue" title="Title">
                               <p>
                                   Put your stuff in here...
                               </p>
                             </div>
 	                    </td>
	                </tr>
	                 <tr>
   	                    <td colspan="3" class="commit-cell"><button id="repo-import-now-button" />Import Now</button></td>
   	                    <td>&nbsp;</td>
   	                </tr>
                </table>
        </div>
        <div id="tabs-response">
             <table id="upload-table">
                    <tr>
                        <td colspan="3">
                            <h3>Import Results</h3>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <p>Your data has been imported and the identifiers allocated to each row are shown below.</p>
                        </td>
                    </tr>
                    <tr>
	                    <th>Results:</th>
	                    <td colspan="2" ><textarea
	                        rows="20"
	                        name="spreadsheet_results"
	                        id="spreadsheet_results"
	                        class=""
                         /></textarea>
                         </td>
	                    <td class="help-cell">
 	                        <a href="#" class="repo-context-help" >?</a>
 	                        <div class="repo-help-dialogue" title="Title">
                               <p>
                                   Put your stuff in here...
                               </p>
                             </div>
 	                    </td>
	                </tr>
	                 <tr>
   	                    <td colspan="3" class="commit-cell"><button id="repo-import-now-button" />Import Now</button></td>
   	                    <td>&nbsp;</td>
   	                </tr>
                </table>
        </div>


    <div> <!-- end tabs -->


<?php
    require_once( '../../inc/footer.php' );
?>