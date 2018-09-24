// Code behind.
"use strict";

var CODE_BEHIND = {
  refresh: refresh,
  mapPatient: mapPatient,
  mapPatientHeader: mapPatientHeader
};


var $ = require("dom");
var SvcCarecenter = require("soin.svc-carecenter");


function refresh() {
  var that = this;

  SvcCarecenter.listPatients( this.id ).then(function( patients ) {
    that.name = patients.name;
    that.patients = patients.list;
    that.actionUptodate = 1;
  });
}


function mapPatientHeader( list ) {
  list.sort(function(a, b) {
    var lastnameA = (a['#PATIENT-LASTNAME'] || '').toLowerCase();
    var lastnameB = (b['#PATIENT-LASTNAME'] || '').toLowerCase();
    if( lastnameA < lastnameB ) return -1;
    if( lastnameA > lastnameB ) return +1;
    var firstnameA = (a['#PATIENT-FIRSTNAME'] || '').toLowerCase();
    var firstnameB = (b['#PATIENT-FIRSTNAME'] || '').toLowerCase();
    if( firstnameA < firstnameB ) return -1;
    if( firstnameA > firstnameB ) return +1;
    var secondnameA = (a['#PATIENT-SECONDNAME'] || '').toLowerCase();
    var secondnameB = (b['#PATIENT-SECONDNAME'] || '').toLowerCase();
    if( secondnameA < secondnameB ) return -1;
    if( secondnameA > secondnameB ) return +1;
    return 0;
  });
}

function mapPatient( patient ) {
  var lastname = patient['#PATIENT-LASTNAME'];
  var firstname = patient['#PATIENT-FIRSTNAME'];
  var secondname = patient['#PATIENT-SECONDNAME'];
  
  var div = $.div([$.tag('span', 'lastname', [lastname])]);
  if( firstname ) $.add( div, [$.tag('span', 'firstname', [firstname])]);
  if( secondname ) $.add( div, [$.tag('span', 'secondname', [secondname])]);
  
  return div;
}
