# Uploads
Es una pequeña clase para manejar los archivos subidos al servidor
# Funcionamiento
Se instancia un objeto de clase Uploads pasando como argumento un array que es la configuración de la clase, el sig. array es la configuración por defecto
```php
$default = [];
// la carpeta donde se guardan los archivos
$default['path'] = getcwd().DIRECTORY_SEPARATOR;
// Función anónima que genera el path del los archivos subidos
$default['rename'] = function (&$file = null, $path = '' )
{
	return $path.$file['name'];
};
// Función anónima que funciona como filtro, este tiene que retornar true o false
$default['filter'] = function (&$file = null) {
	return (is_uploaded_file($file['tmp_name']) and $file['error'] == UPLOAD_ERR_OK )?true:false;
};
// Nombre de key de $_FILES, eje. $_FILES['files']
$default['name'] = 'files';
```
Las funciones anónimas reciben como parámetro $file que es un array que representa el archivo subido
```php
$file = [
	name     => 'MyFile.jpg',
	type     => 'image/jpeg',
	tmp_name => '/tmp/php/php6hst32',
	error    => 0,
	size     => '98174',
];
```
El objeto de la clase Uploads, representa un array los archivos subidos (objetos de la clase UploadedFile)
```php
$files = new Uploads();
foreach ($files as $file) {
	echo $file->getError(); // muestra el error del archivo
}
for ($i = 0; $i < sizeof($files) ; $i++) {
	echo $file[$i]->formatSize(); // muestra el tamaño con formato
}
// métodos de la clase Uploads
$files->move();       // mueve todos los archivos a su path
$files->delete();     // borra los archivos
$files->getFiles();   // retorna el array de objetos de la clase UploadedFile
$files->formatSize(); // retorna el tamaño de los archivos subidos con formato
$files->asArray();    // retorna en forma de array los archivos

$files->size;         // retorna el tamaño de los archivos subidos
$files->fails;        // retorna el total de fallos

// métodos de la clase UploadedFile

$files->getError();   // retorna el error del archivo
$files->move();       // mueve el archivo, recibe argumento opcional el path donde se moverá si no se moverá,
			// path por definido en $files->path
$files->fail();       // retorna true si el archivo tiene un fallo
$files->delete();     // borra el archivo
$files->asArray();    // retorna en formato array
$files->formatSize(); // muestra el tamaño con formato

$files->name;         // nombre del archivo
$files->type;         // mime del archivo
$files->tmp_name;     // nombre temporal
$files->error;        // código de error
$files->size;         // tamaño del archivo
$files->path;         // el path donde se guardaran el archivo

```