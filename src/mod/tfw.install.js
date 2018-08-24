/*
 * var Install = require("tfw.install");
 * Intall.check( "cameroun", start );
 */
"use strict";

var CODE_BEHIND = {
  onFocus: onFocus,
  check: staticCheck
};

var $ = require("dom");
var WS = require("tfw.web-service");
var PM = require("tfw.binding.property-manager");
var Err = require("tfw.message").error;
var Modal = require("wdg.modal");
var Button = require("tfw.view.button");


function onFocus( id ) {
  var field = this.$elements[id];
  if( field ) field.focus = true;
}

/* 
 *  First step:
 *  -----------
 *  Call tfw.Install("cameroun").
 *  If the result is 0, everything is already installed.
 *  Otherwise, the result will be -9.
 * 
 *  Second step:
 *  ------------
 *  Call tfw.Install({
 *  prefix: "cameroun_",
 *  host: "127.0.0.1",
 *  name: "database-name",
 *  dbUsr: "root",
 *  dbPwd: "database-password",
 *  usr: "Website-user",
 *  pwd: "user-password"
 *  })

 * The file "pri/install.sql" will be applied to the database. This file is a MySQL file with these placeholders:
 * * `{PREFIX}`
 * * `{USER}`
 * * `{PASSWORD}`
 * * `{DATE}`
 * 
 *  Here are the possible results :
 *  *  0: Installation done successfully.
 *  * -1: Missing mandatory argument.
 *  * -2: Unknown host.
 *  * -3: Database not found.
 *  * -4: Invalid user name or password.
 *  * -5: Missing "pri/install.sql" file.
 *  * -6: DB is not empty.
 */
function staticCheck( prefix, onCheckDone ) {
  var View = this;
  WS.get("tfw.Install", prefix).then(
    function( retCode ) {
      if( retCode !== 0 ) {
        askForInstallInformation( View, prefix, onCheckDone );
      } else {
        onCheckDone();
      }
    }
  );
}


function askForInstallInformation( View, prefix, onCheckDone ) {
  var btnOk = new Button({
    text: _("install"),
    icon: "ok",
    type: "primary"
  });
  var view = new View();
  var modal = new Modal({
    header: _("title"),
    content: view,
    footer: [btnOk]
  });
  modal.attach();

  PM( btnOk ).on("action", function () {
    btnOk.wait = true;
    var fields = ['host', 'name', 'dbUsr', 'dbPwd', 'usr', 'pwd'];
    var field;
    for( var k = 0; k < fields.length; k++ ) {
      field = fields[k];
      if( view[field].trim().length === 0 ) {        
        view.focus = field;
        Err(_("mandatory"));
        return;
      }
    }
    WS.get("tfw.Install", {
      prefix: prefix,
      host: view.host,
      name: view.name,
      dbUsr: view.dbUsr,
      dbPwd: view.dbPwd,
      usr: view.usr,
      pwd: view.pwd
    }).then(
      function(retCode) {
        btnOk.wait = false;
        if( retCode !== 0 ) {
          showError( retCode, view );
        } else {
          modal.detach();
          onCheckDone();
        }
      }
    );
  });

  window.setTimeout(function() {
    view.focus = true;
  }, 500);
}


function showError( errorCode, view ) {
  var btnOk = new Button({ text: _('ok'), flat: true });
  var modal = new Modal({
    header: _('error'),
    content: $.div('tfw-install-error', [_('error' + errorCode, view.host, view.name)]),
    footer: btnOk
  });
  PM( btnOk ).on( "action", function() {
    modal.detach();
    view.focus = true;
  });
  modal.attach();
}
