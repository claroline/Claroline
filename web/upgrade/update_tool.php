<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include __DIR__ . '/authorize.php';
include __DIR__ . '/libs.php';

$vendorDir = __DIR__ . "/../../vendor";

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Claroline upgrader</title>
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css">
        <link rel="shortcut icon" href="../claroline.ico" />
        <script src="libs.js"></script>
        <script src="//code.jquery.com/jquery-2.1.1.min.js"></script>
        <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
        <style>
            body { background: #E2E2E2; }
            .navbar {
                height: 53px;
                background: #428BCA;
                border-bottom: 3px solid #f89406;
                -webkit-box-shadow: 0 2px 3px rgba(0 ,0, 0 , 0.25);
                box-shadow: 0 2px 3px rgba(0, 0, 0, 0.25);
            }
            .navbar img { height: 35px; margin: 7px auto; }
            .required { color: #777; font-weight: normal; }
            .info-txt { margin: auto auto 24px; }
            .panel-body { max-width: 94%; margin: 24px; }
            .step-controls { margin: 14px 14% auto; }
        </style>
    </head>
    <body>
		<div id="data" data-locale='<?php echo isset($_GET['_locale']) ? $_GET['_locale']: 'en' ?>'></div>
        <nav class="navbar navbar-static-top navbar-inverse" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <img src="../uploads/logos/clarolineconnect.png"/>
                </div>
            </div>
        </nav>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3><?php Translator::translate('upgrade_tool'); ?> (beta)</h3>
                        </div>
                        <div class="panel-body">
                            <div>
                                <input type="checkbox" id="debug-mode"><?php Translator::translate('debug_mode') ?></input>
                                </br><?php Translator::translate('debug_mode_explanation') ?>
                            </div>
							<div class="well">
								<h4><?php Translator::translate('upgrade_steps'); ?></h4>
								<ul>
									<li> 1 - <?php Translator::translate('create_backup'); ?> </li>
                                    <li> 2 - <?php Translator::translate('activate_maintenance_mode'); ?>
									<li> 3 - <?php Translator::translate('vendor_replacement'); ?></li>
									<li> 4 - <?php Translator::translate('executing_migrations'); ?> </li>
                                    <li> 5 - <?php Translator::translate('remove_maintenance_mode'); ?> </li>
								</ul>
							</div>
                            <a id="start-btn" class="btn btn-primary" data-toggle="modal" data-target="#upgrade-modal">
                                <?php Translator::translate('start'); ?>
                            </a>
                            <a id="return-btn" href=".." class="btn btn-danger">
                                <?php Translator::translate('return'); ?>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!----------------------- MODAL -------------------------------->

        <div class="modal fade" id="upgrade-modal">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title"></h4>
			  </div>
			  <div class="modal-body">
				  <p id="content-modal")></p>
				  <div id="log-container" class="row">
					<pre id="log-content" class="executable"
						 style="max-height: 150px; overflow: auto; display: none"
						 data-url="refresh.php">
					</pre>
				  </div>
			  </div>

			  <div class="modal-footer">
				<button id="execute-btn" type="button" class="btn btn-primary executable">Execute</button>
				<button id="back-btn" type="button" class="btn btn-primary">Back</button>
				<button id="next-btn" type="button" class="btn btn-primary">Next</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			  </div>
			</div>
		  </div>
		</div>

		<!---------------- END MODAL -------------------------------->

        <script>
			//installation steps
			var currentStep = 0;
            var now = Date.now();
            var isDebug = false;
			var steps = [
				{
					'title': 'backup_title',
					'content': 'backup_content',
					'action': null,
					'logFile': null,
                    'successAlert': null,
                    'errorAlert': null
				},
                {
                    'title': 'activate_maintenance_title',
                    'content': 'activate_maintenance_content',
                    'action': 'maintenance.php?on=1',
                    'logFile': null,
                    'successAlert': 'maintenance_enabled',
                    'errorAlert': null
                },
				{
					'title': 'replace_vendor_title',
					'content': 'replace_vendor_content',
					'action': null,
					'logFile': null,
                    'successAlert': null,
                    'errorAlert': null
				},
				{
					'title': 'post_update_title',
					'content': 'post_update_content',
					'action': 'post_update.php?logId=' + now,
					'logFile': 'post_update-' + now,
                    'successAlert': null,
                    'errorAlert': 'upgrade_error'
				},
                {
                    'title': 'disable_maintenance_title',
                    'content': 'disable_maintenance_content',
                    'action': 'maintenance.php?on=0',
                    'logFile': null,
                    'successAlert': 'maintenance_disabled',
                    'errorAlert': null
                }
			];

			//initialize objects
			var translator   = new Translator();
			translator.setLocale($('#data').attr('data-locale'));
			var logDisplayer = new LogDisplayer('#log-content');
            var executedSteps = [];

			var setStepExecutable = function() {
				$('.executable').show();
				logDisplayer.setLogFile(steps[currentStep].logFile);
				logDisplayer.start();
                $('#log-content').hide();
                executedSteps.indexOf(currentStep) != -1  ?
                    $('#next-btn').prop('disabled', false):
                    $('#next-btn').prop('disabled', true);

                //if mode debug, the next button must always be available
                if (isDebug) $('#next-btn').prop('disabled', false);
			};

			var setStepUnexecutable = function() {
				$('.executable').hide();
				logDisplayer.stop();
                $('#next-btn').prop('disabled', false);
			};

            var hideNextPrev = function()
            {
                if (currentStep === steps.length - 1) {
                    $('#next-btn').hide()
                } else {
                    $('#next-btn').show();
                    //show end button
                }

                currentStep === 0 ?
                    $('#back-btn').hide(): $('#back-btn').show();
            }

			//initial modalbox content
			$('#content-modal').html(translator.translate(steps[currentStep].content));
			$('.modal-title').html(currentStep + 1 + ' - ' + translator.translate(steps[currentStep].title));
			steps[currentStep].action ? setStepExecutable(): setStepUnexecutable();

			//event driven functions
			$('#next-btn').on('click', function(event) {
				currentStep = currentStep + 1 >= steps.length ? currentStep: currentStep += 1;
				steps[currentStep].action ? setStepExecutable(): setStepUnexecutable();
				$('#content-modal').html(translator.translate(steps[currentStep].content));
				$('.modal-title').html(currentStep + 1 + ' - ' + translator.translate(steps[currentStep].title));
                hideNextPrev();
			});

			$('#back-btn').on('click', function(event) {
				currentStep = currentStep - 1 < 0 ? currentStep: currentStep -= 1;
				steps[currentStep].action ? setStepExecutable(): setStepUnexecutable();
				$('#content-modal').html(translator.translate(steps[currentStep].content));
				$('.modal-title').html(currentStep + 1 + ' - ' + translator.translate(steps[currentStep].title));
                hideNextPrev();
			});

			$('#execute-btn').on('click', function(event) {
				var action = steps[currentStep].action;
                if (steps[currentStep].logFile) $('#log-content').show();
				$.ajax({
					url: action,
                    success: function (data) {
                        executedSteps.push(currentStep);
                        if (steps[currentStep].successAlert)
                            alert(translator.translate(steps[currentStep].successAlert));
                        $('#next-btn').prop('disabled', false);
                        logDisplayer.end();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        //debug the error for now. It doesn't handle fatal errors yet.
                        console.debug(jqXHR);
                        console.debug(textStatus);
                        console.debug(errorThrown);
                    }
				});
			});

            $('#debug-mode').change(function() {
                isDebug = $('#debug-mode').is(':checked');
                if (isDebug) $('#next-btn').prop('disabled', false);
            });

			$('#upgrade-modal').on('hidden.bs.modal', function (e) {
				logDisplayer.stop();
			});

			$('#upgrade-modal').on('shown.bs.modal', function (e) {
                hideNextPrev();
				if (steps[currentStep].logFile) logDisplayer.start();
			});
        </script>
    </body>
</html>
