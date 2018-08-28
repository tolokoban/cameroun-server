"use strict";

var $ = require("dom");
var Combo = require("tfw.view.combo");
var Button = require("tfw.view.button");


exports.start = function() {
  var cboTrueFalse = new Combo({
    label: "Do you agree?",
    items: ["Yes", "No"],
    value: 1
  });
  var combo = new Combo({
    label: "hello world!",
    items: ["John", "Paul", "Brigitte", "Vanessa", "The best woman in the world!"],
    keys: ["john", "paul", "brigitte", "vanessa", "best"],
    value: "brigitte"
  });

  var btnVanessa = new Button({ text: "Vanessa" });
  btnVanessa.on(function() {
    combo.value = "vanessa";
  });
  var btnPaul = new Button({ text: "Paul" });
  btnPaul.on(function() {
    combo.value = "paul";
  });
  var btnSee = new Button({ text: "Read combos", flat: true, icon: "show" });
  btnSee.on(function() {
    console.info("[test] combo.value, cboTrueFalse.value=", combo.value, cboTrueFalse.value);
  });

  $.add( document.body, cboTrueFalse );
  $.add( document.body, combo );
  $.add( document.body, btnVanessa, btnPaul, btnSee );
};
