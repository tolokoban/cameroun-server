<?php
class SystemData {
  var $datadir = '';
  var $locks = Array();

  function __construct($datadir) {
    $this->datadir = dirname(__file__).'/../'.$datadir.'/';
    if (!file_exists($datadir)) {
      @mkdir($datadir, 0777, true);
    }
  }

  function setRoot($dir) {
    $this->datadir .= $dir . '/';
    if (!file_exists($this->datadir)) {
      @mkdir($datadir, 0777, true);
    }
  }

  function __destruct() {
    foreach ($this->locks as $key => $fd) {
      echo "Releasing lock on \"$key\" : ";
      flock($fd, LOCK_UN);
      fclose($fd);
      echo "done!\n";
    }
  }

  function lock($filename) {
    if (array_key_exists($filename, $this->locks)) {
      return;
    }

    $fd = fopen($this->getPath($filename . ".lock"), "w");
    while (!flock($fd, LOCK_EX)) {
      usleep(10000);  // sleep for 0.01 second
    }
    $this->locks[$filename] = $fd;
  }

  function unlock($filename) {
    if (array_key_exists($filename, $this->locks)) {
      flock($this->locks[$filename], LOCK_UN);
      fclose($this->locks[$filename]);
      unset($this->locks[$filename]);
    }
  }

  function mkdir($dir) {
    $dir = $this->datadir . $dir;
    if (!file_exists($dir)) {
      @mkdir($dir, 0777, true);
    }
  }

  function getPath($filename) {
    return $this->datadir . $filename;
  }

  function opendir($dir) {
    return opendir($this->datadir . $dir);
  }

  function unlink($filename) {
    return unlink($this->getPath($filename));
  }

  function saveText($filename, $data) {
    if (!file_exists($this->datadir)) {
      @mkdir($this->datadir, 0777, true);
    }
    @file_put_contents($this->datadir.$filename, $data);
  }

  function saveJSON($filename, $data) {
    if (!file_exists($this->datadir)) {
      @mkdir($this->datadir, 0777, true);
    }
    @file_put_contents($this->datadir.$filename, json_encode($data));
  }

  function loadText($filename) {
    $file = $this->datadir.$filename;
    if (file_exists($file)) {
      // L'ajout du paramètre "true" sert à s'assurer que $data
      // sera bien du type Array et non stdClass.
      $data = @file_get_contents($file);
      return $data;
    } else {
      echo "Unable to find \"$file\"!\n";
      return null;
    }
  }

  function loadJSON($filename) {
    $file = $this->datadir.$filename;
    if (file_exists($file)) {
      // L'ajout du paramètre "true" sert à s'assurer que $data
      // sera bien du type Array et non stdClass.
      $data = @json_decode(file_get_contents($file), true);
      return $data;
    } else {
      echo "Unable to find \"$file\"!\n";
      return null;
    }
  }
};


class System {
  var $PUB;
  var $PRI;
  var $DB = null;

  function __constructor() {
    $this->PUB = new SystemData("pub");
    $this->PRI = new SystemData("pri");
  }
};
?>
