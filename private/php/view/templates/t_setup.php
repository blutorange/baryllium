<html>
    <head>
        <title>Database setup</title>
        <meta charset="UTF-8">
        <style>
            label,input,select {
                display: block;
                width: 100%;
            }
            label {
                margin-bottom: 1em;
            }
            span.required-star {
                color: red;
                font-weight: 400;
            }
            header {
                border: 1px solid red;
                margin-bottom: 1em;
            }
            .load {
                position: fixed;
                left: 0;
                top: 0;
                width:100vw;
                height: 100vh;
                background-color: rgba(247,247,247,0.9);
            }
            .load p {
                font-weight: bold;
                position: fixed;
                left: 48vw;
                top: 48vh;
                width: 100vw;
                height: 100vh;
            }
            .hidden {
                display: none;
            }
        </style>
        <script type="text/javascript">
            window.overlay = function () {
                document.getElementById('loadOverlay').classList.remove("hidden")
            };
        </script>
    </head>
    <body>
        <div class="load hidden" id="loadOverlay">
            <p>Working...</p>
        </div>
        <?php if (!empty($message)) : ?>
            <header>
                <p>Database connection failed, see below for details.</p>
                <details class="panel panel-default">
                    <summary class="panel-heading"><?= $this->e($message) ?></summary>
                    <pre class="panel-body"><?= $this->e($detail) ?></pre>
                </details>
            </header>
        <?php endif; ?>
        <form novalidate method="post" data-bootstrap-parsley action="<?= $this->e($action) ?>">
            <fieldset>
                <legend>Basic setup</legend>
                <label for="sysmail">
                    System mail address for sending mails <span class=required-star>*</span>
                    <input required id="sysmail" name="sysmail" type="text" placeholder="admin@example.com" value="admin@example.com"/>
                </label>
            </fieldset>
            <fieldset>
                <legend>Database setup</legend>

                <label for="host">
                    Host <span class=required-star>*</span>
                    <input required id="host" name="host" type="text" placeholder="127.0.0.1" value="127.0.0.1"/>
                </label>
                
                <label for="port">
                    Port
                    <input id="port" name="port" type="number" placeholder="3306"/>
                </label>
                
                <label for="driver">
                    Database type <span class=required-star>*</span>
                    <select required id="driver" name="driver">
                        <option value="">Please select</option>
                        <option value="mysql">MySQL</option>
                        <option value="oracle">OracleDB</option>
                        <option value="sqlite">SQLite</option>
                    </select>
                </label>
                
                <label for="dbname">
                    Database name <span class=required-star>*</span>
                    <input required id="dbname" name="dbname" type="text" placeholder="baryllium" value="baryllium"/>
                </label>
                
                <label for="dbnameDev">
                    Database name (development)
                    <input required id="dbnameDev" name="dbnameDev" type="text" placeholder="baryllium-dev" value="baryllium-dev"/>
                </label>
                
                <label for="dbnameTest">
                    Database name (testing)
                    <input required id="dbnameTest" name="dbnameTest" type="text" placeholder="baryllium-test" value="baryllium-test"/>
                </label>
                
                <label for="user">
                    User <span class=required-star>*</span>
                    (must have all permissions for the database)
                    <input required value="baryllium" id="user" name="user" type="text" placeholder="baryllium"/>
                </label>
                
                <label for="pass">
                    Password <span class=required-star>*</span>
                    <input required id="pass" name="pass" type="password"/>
                </label>

                <label for="collation">
                    Collation <span class=required-star>*</span>
                    <input required id="collation" value="utf8_general_ci" name="collation" type="text" placeholder="utf8_general_ci"/>
                </label>
                               
                <label for="encoding">
                    Collation <span class=required-star>*</span>
                    <input required id="encoding" value="utf8" name="encoding" type="text" placeholder="utf8""/>
                </label>
                
                <input type="submit" onclick="overlay()"/>                
            </fieldset>    
        </form>
    </body>        
</html>