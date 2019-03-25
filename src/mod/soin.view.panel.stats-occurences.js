"use strict";


var CODE_BEHIND = { refresh, toDate, onExportPatients, onExportData };


//############################################################

const FileAPI = require("tfw.fileapi");

var SvcStats = require("soin.svc-occurences");


function refresh() {
  const that = this;
  const d = new Date( 1000 * this.begin );
  d.setMonth( d.getMonth() + 1 );
  const end = Math.floor( d.getTime() * 0.001 );
  this.end = end;

  SvcStats.list( this.id, this.begin, end ).then(
    function( data ) {
      that.patients = data.patients;
      that.data = data.data;
      that.actionUptodate = 1;
      that.patientsCount = Object.keys(that.patients).length;
      that.consultationsCount = Object.keys(that.data).length;
    }
  );
}


function toDate( value ) {
  const d = new Date( 1000 * value );
  return d.toLocaleString();
}


function onExportPatients() {
  exportFile( this.patients, "patients.xls" );
}


function onExportData() {
  exportFile( this.data, "consultations.xls" );
}


function exportFile( data, filename ) {
  const keys = {};
  Object.keys(data).forEach(id => {
    const item = data[id];
    Object.keys(item).forEach(key => {
      keys[key] = "";
    });
  });

  let content = Object.keys(keys).map(JSON.stringify).join("\t");

  Object.keys(data).forEach(id => {
    const item = data[id];
    const row = Object.assign( keys, item );
    content += "\n" + Object.keys(row).map(k => JSON.stringify( convert( k, row[k] ) )).join("\t");
  });

  FileAPI.saveAs( new Blob([ content ]), filename );
}


function convert( key, val ) {
  switch( key.trim().toUpperCase() ) {
  case 'DATE':
    const d = new Date( 1000 * parseInt(val, 10) );
    return `${d.getFullYear()}/${d.getMonth() + 1}/${d.getDate()}`;
  }
  return val;
}
