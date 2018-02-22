<?php
class UploadedFile
{
	protected $atrr =[];
	public function __construct($atrr)
	{
		$this->atrr = $atrr;
	}
	public function __isset($name){
		return isset($this->atrr[$name]);
	}
	public function set($name, $value)
	{
		$this->data[$name] = $value;
	}
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}
	function get($name)
	{
		if (array_key_exists($name, $this->atrr)) {
			return $this->atrr[$name];
		}
		$trace = debug_backtrace();
		trigger_error('Propiedad indefinida mediante: ' . $name .' en ' . $trace[0]['file'] .' en la línea ' . $trace[0]['line'],E_USER_NOTICE);
		return null;
	}
	public function __get($name){
		return $this->get($name);
	}
	public function getError()
	{
		switch ($this->atrr['error']) {
			case UPLOAD_ERR_OK: return 'There is no error, the file uploaded with success.';
			case UPLOAD_ERR_INI_SIZE: return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
			case UPLOAD_ERR_FORM_SIZE: return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
			case UPLOAD_ERR_PARTIAL: return 'The uploaded file was only partially uploaded.';
			case UPLOAD_ERR_NO_FILE: return 'No file was uploaded.';
			case UPLOAD_ERR_NO_TMP_DIR: return 'Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.';
			case UPLOAD_ERR_CANT_WRITE: return 'Failed to write file to disk. Introduced in PHP 5.1.0.';
			case UPLOAD_ERR_EXTENSION: return 'File upload stopped by extension. Introduced in PHP 5.2.0.';
			default: return 'Unknown upload error';
		}
	}
	public function move($path = null )
	{
		if (isset($this->atrr['moved'])) {
			return $this->atrr['moved'];
		}

		if ($this->atrr['error'] != UPLOAD_ERR_OK) {
			return false;
		}

		if (is_null($path)) {
			if (!isset($this->atrr['path'])) {
				return false;
			}
			$path = $this->atrr['path'];
		}

		if (move_uploaded_file($this->atrr['tmp_name'],$path)) {
			$this->atrr['moved'] = true;
		}else {
			$this->atrr['moved'] = false;
		}
		return $this->atrr['moved'];
	}
	public function fail()
	{
		if ($this->atrr['error'] == UPLOAD_ERR_OK) {
			return false;
		}else {
			return true;
		}
	}
	public function delete()
	{
		if ($atrr['moved']) {
			unlink($this->atrr['path']);
		}
	}
	public function asArray()
	{
		return $this->atrr;
	}
	public function formatSize($array = false, $precision = 2)
	{
		$bytes = $this->atrr['size'];
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
	   	$bytes = max($bytes, 0);
	   	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	   	$pow = min($pow, count($units) - 1);

    	// Uncomment one of the following alternatives
    	// $bytes /= pow(1024, $pow);
    	$bytes /= (1 << (10 * $pow));

    	if ($array) {
    		return ['size' => round($bytes, $precision), 'unit' => $units[$pow]];
    	}else{
	    	return round($bytes, $precision) . ' ' . $units[$pow];
    	}
	}

}
class Uploads implements IteratorAggregate, ArrayAccess, Countable
{
	protected $files =[];
	protected $atrr =[];
	public function __construct($conf = [])
	{
		$default = [
			'path' => getcwd().DIRECTORY_SEPARATOR,
			'rename' => function (&$file = null, $path = '' ) { return $path.$file['name']; },
			'filter' => function (&$file = null) { return (is_uploaded_file($file['tmp_name']) and $file['error'] == UPLOAD_ERR_OK )?true:false;},
			'name' => 'files',
		];
		$conf = array_merge($default, $conf);
		extract($conf);
		/*
         *   [name] => MyFile.jpg
         *   [type] => image/jpeg
         *   [tmp_name] => /tmp/php/php6hst32
         *   [error] => UPLOAD_ERR_OK
         *   [size] => 98174
         */
		if (!isset($_FILES[$name])) {
			$trace = debug_backtrace();
			trigger_error("No esta definido _FILES[".$name."] en  ". $trace[0]['file'] .' en la línea ' . $trace[0]['line'],E_USER_NOTICE);
		}
		foreach($_FILES[$name] as $atrr => $file){
			foreach($file as $index => $value){
				$files[$index][$atrr] = $value;
			}
		}
		$this->atrr['size'] = 0 ;
		$this->atrr['fails'] = 0 ;
		for ($i = 0; $i < sizeof($files); $i++) {
		 	if ($filter($files[$i])) {
		 		$files[$i]['path'] = $rename($files[$i], $path);
		 		$this->atrr['size'] += $files[$i]['size'];
		 		$this->files[] = new UploadedFile($files[$i]);
		 	}else{
		 		$this->atrr['fails']++;
		 	}
		}
	}
	public function move()
	{
		foreach ($this->files as $file) {
			$file->move();
		}
	}
	public function delete()
	{
		foreach ($this->files as $file) {
			$file->delete();
		}
	}
	public function getFiles()
	{
		return $this->files;
	}
    public function getIterator() {
        return new ArrayIterator($this->files);
    }
    public function count()
    {
    	return count($this->files);
    }
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->$files[] = $value;
        } else {
            $this->$files[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->$files[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->$files[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->$files[$offset]) ? $this->$files[$offset] : null;
    }
	function get($name)
	{
		if (array_key_exists($name, $this->atrr)) {
			return $this->atrr[$name];
		}
		$trace = debug_backtrace();
		trigger_error('Propiedad indefinida mediante: ' . $name .' en ' . $trace[0]['file'] .' en la línea ' . $trace[0]['line'],E_USER_NOTICE);
		return null;
	}
	public function __get($name){
		return $this->get($name);
	}
	public function formatSize($array = false, $precision = 2)
	{
		$bytes = $this->atrr['size'];
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
	   	$bytes = max($bytes, 0);
	   	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	   	$pow = min($pow, count($units) - 1);

    	// Uncomment one of the following alternatives
    	// $bytes /= pow(1024, $pow);
    	$bytes /= (1 << (10 * $pow));

    	if ($array) {
    		return ['size' => round($bytes, $precision), 'unit' => $units[$pow]];
    	}else{
	    	return round($bytes, $precision) . ' ' . $units[$pow];
    	}
	}
	public function asArray()
	{
		$array = [];
		foreach ($this->files as $file) {
			$array[] = $file->asArray();
		}
		return $array;
	}
	public function __isset($name){
		return isset($this->atrr[$name]);
	}
}