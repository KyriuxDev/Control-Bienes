# Control-Bienes

## Base de datos

~~~sql
-- Tabla de trabajador/usuarios
CREATE TABLE trabajador (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(200) NOT NULL,
    cargo VARCHAR(200),
    institucion VARCHAR(200),
    adscripcion VARCHAR(200),   
    matricula VARCHAR(50) UNIQUE NOT NULL,
    identificacion VARCHAR(100),
    direccion TEXT,
    telefono VARCHAR(100),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de bien
CREATE TABLE bien (
    id INT PRIMARY KEY AUTO_INCREMENT,
    identificacion VARCHAR(100),
    descripcion TEXT NOT NULL,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    serie VARCHAR(100),
    naturaleza ENUM('BC', 'BMNC', 'BMC', 'BPS') NOT NULL,
    estado_fisico TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de préstamos (prestamo1.pdf)
CREATE TABLE prestamo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    folio VARCHAR(50) UNIQUE,
    trabajador_id INT NOT NULL,
    fecha_emision DATE NOT NULL,
    fecha_devolucion_programada DATE NOT NULL,
    fecha_devolucion_real DATE,
    lugar VARCHAR(200),
    matricula_autoriza VARCHAR(50),
    matricula_recibe VARCHAR(50),
    estado ENUM('ACTIVO', 'DEVUELTO', 'VENCIDO') DEFAULT 'ACTIVO',
    observaciones TEXT,
    FOREIGN KEY (trabajador_id) REFERENCES trabajador(id),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de detalle de bien en préstamo
CREATE TABLE prestamo_detalle (
    id INT PRIMARY KEY AUTO_INCREMENT,
    prestamo_id INT NOT NULL,
    bien_id INT NOT NULL,
    cantidad INT DEFAULT 1,
    FOREIGN KEY (prestamo_id) REFERENCES prestamo(id) ON DELETE CASCADE,
    FOREIGN KEY (bien_id) REFERENCES bien(id)
);

-- Tabla de resguardo (resguardo1.pdf)
CREATE TABLE resguardo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    folio VARCHAR(50) UNIQUE,
    trabajador_id INT NOT NULL,
    bien_id INT NOT NULL,
    fecha_asignacion DATE NOT NULL,
    fecha_devolucion DATE,
    lugar VARCHAR(200),
    estado ENUM('ACTIVO', 'DEVUELTO') DEFAULT 'ACTIVO',
    notas_adicionales TEXT,
    FOREIGN KEY (trabajador_id) REFERENCES trabajador(id),
    FOREIGN KEY (bien_id) REFERENCES bien(id),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de salidas de bien (salidaBiene.pdf)
CREATE TABLE salida_bien (
    id INT PRIMARY KEY AUTO_INCREMENT,
    folio VARCHAR(50) UNIQUE,
    trabajador_id INT NOT NULL,
    area_origen VARCHAR(200),
    destino VARCHAR(200),
    fecha_salida DATE NOT NULL,
    fecha_devolucion_programada DATE,
    sujeto_devolucion BOOLEAN DEFAULT TRUE,
    lugar VARCHAR(200),
    observaciones_estado TEXT,
    estado ENUM('AUTORIZADO', 'EN_TRANSITO', 'DEVUELTO') DEFAULT 'AUTORIZADO',
    FOREIGN KEY (trabajador_id) REFERENCES trabajador(id),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de detalle de bien en salida
CREATE TABLE salida_detalle (
    id INT PRIMARY KEY AUTO_INCREMENT,
    salida_id INT NOT NULL,
    bien_id INT NOT NULL,
    cantidad INT DEFAULT 1,
    FOREIGN KEY (salida_id) REFERENCES salida_bien(id) ON DELETE CASCADE,
    FOREIGN KEY (bien_id) REFERENCES bien(id)
);

-- Índices para mejorar el rendimiento
CREATE INDEX idx_trabajador_matricula ON trabajador(matricula);
CREATE INDEX idx_prestamo_estado ON prestamo(estado);
CREATE INDEX idx_resguardo_estado ON resguardo(estado);
CREATE INDEX idx_salidas_estado ON salida_bien(estado);
CREATE INDEX idx_bien_naturaleza ON bien(naturaleza);



~~~

## Composer
kyriux@debian:/opt/lampp/htdocs/imss-control-bienes$ cd /opt/lampp/bin
kyriux@debian:/opt/lampp/bin$ sudo wget https://getcomposer.org/download/1.10.26/composer.phar
[sudo] contraseña para kyriux: 
--2026-01-02 13:38:37--  https://getcomposer.org/download/1.10.26/composer.phar
Resolviendo getcomposer.org (getcomposer.org)... 54.39.182.210, 2607:5300:201:2100::6e1
Conectando con getcomposer.org (getcomposer.org)[54.39.182.210]:443... conectado.
Petición HTTP enviada, esperando respuesta... 200 OK
Longitud: 1993462 (1.9M) [application/octet-stream]
Grabando a: «composer.phar»

composer.phar       100%[===================>]   1.90M  2.08MB/s    en 0.9s    

2026-01-02 13:38:39 (2.08 MB/s) - «composer.phar» guardado [1993462/1993462]

kyriux@debian:/opt/lampp/bin$ sudo chmod +x composer.phar
kyriux@debian:/opt/lampp/bin$ /opt/lampp/bin/php composer.phar --version
Composer version 1.10.26 2022-04-13 16:39:56
kyriux@debian:/opt/lampp/bin$ 

kyriux@debian:/opt/lampp/htdocs/imss-control-bienes$ /opt/lampp/bin/php /opt/lampp/bin/composer.phar dump-autoload
Generating autoload files
Generated autoload files

## Database Independence

### Domain Models

~~~ php

<?php
namespace App\Domain\Entity;

abstract class AbstractEntity
{
    protected $id;

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
        return $this;
    }
}

~~~

### Domain Services

- Repositories
- Factories
- Services

#### Repositores

Las interfaces de repository son contratos que definen qué métodos debe implementar cada repositorio, sin especificar cómo se implementan.
¿Para qué sirven?

Contrato: Garantizan que todos los repositorios tengan ciertos métodos (getById, getAll, persist, begin, commit)
Flexibilidad: Puedes cambiar la implementación (de MySQL a PostgreSQL, por ejemplo) sin cambiar el código que usa el repositorio
Testing: Facilitan crear mocks para pruebas unitarias
Inyección de dependencias: Permiten inyectar repositorios sin depender de implementaciones concretas

~~~ php
<?php
namespace App\Domain\Repository;

interface RepositoryInterface {

    public function getById($id);
    public function getAll();
    public function persist($entity);
    public function begin();
    public function commit();
}


~~~
###