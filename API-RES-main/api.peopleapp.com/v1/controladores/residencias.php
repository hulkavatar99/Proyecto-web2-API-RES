<?php

class residencias {

    const NOMBRE_TABLA = "residencias";
    const ID_RESIDENCIA = "idResidencia";
    const NOMBRE_RESIDENCIA = "nombreResidencia";
    const NUM_INTEGRANTES = "numIntegrantes";
    const FECHA_INICIO = "fechaInicio";
    const CARRERA = "carrera";
    const ID_USUARIO = "idUsuario";

    const CODIGO_EXITO = 1;
    const ESTADO_EXITO = 1;
    const ESTADO_ERROR = 2;
    const ESTADO_ERROR_BD = 3;
    const ESTADO_ERROR_PARAMETROS = 4;
    const ESTADO_NO_ENCONTRADO = 5;


    public static function get($peticion)
    {
        $idUsuario = usuarios::autorizar();

        if (empty($peticion[0]))
            return self::obtenerResidencias($idUsuario);
        else
            return self::obtenerResidencias($idUsuario, $peticion[0]);

    }

    private static function obtenerResidencias($idUsuario, $idResidencia = NULL)
    {
        try {
            if (!$idResidencia) {

                $comando = "SELECT * FROM " . self::NOMBRE_TABLA .
                    " WHERE " . self::ID_USUARIO . "=?";

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                // Ligar idUsuario
                $sentencia->bindParam(1, $idUsuario, PDO::PARAM_INT);

            } else {

                $comando = "SELECT * FROM " . self::NOMBRE_TABLA .
                    " WHERE " . self::ID_RESIDENCIA . "=? AND " .
                    self::ID_USUARIO . "=?";

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                // Ligar idContacto e idUsuario
                $sentencia->bindParam(1, $idResidencia, PDO::PARAM_INT);
                $sentencia->bindParam(2, $idUsuario, PDO::PARAM_INT);
            }

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "estado" => self::ESTADO_EXITO,
                        "datos" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }


    public static function post($peticion)
    {
        $idUsuario = usuarios::autorizar();

        

        $body = file_get_contents('php://input');
        $residencia = json_decode($body);

        $idResidencia = residencias::crear($idUsuario, $residencia);
      
        http_response_code(201);
        return [
            "estado" => self::CODIGO_EXITO,
            "mensaje" => "Residencia creada",
            "id" => $idResidencia
        ];

    }

     /**
     * Añade un nuevo contacto asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param mixed $residencia datos del residencia
     * @return string identificador de la residencia
     * @throws ExcepcionApi
     */
    private static function crear($idUsuario, $residencia)
    {

        if ($residencia) {
            try {

                $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

                // Sentencia INSERT
                $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                    self::NOMBRE_RESIDENCIA . "," .
                    self::NUM_INTEGRANTES . "," .
                    self::FECHA_INICIO . "," .
                    self::CARRERA . "," .
                    self::ID_USUARIO . ")" .
                    " VALUES(?,?,?,?,?)";

                // Preparar la sentencia
                $sentencia = $pdo->prepare($comando);

                $sentencia->bindParam(1, $nombreResidencia);
                $sentencia->bindParam(2, $numIntegrantes);
                $sentencia->bindParam(3, $fechaInicio);
                $sentencia->bindParam(4, $carrera);
                $sentencia->bindParam(5, $idUsuario);


                $nombreResidencia = $residencia->nombreResidencia;
                $numIntegrantes = $residencia->numIntegrantes;
                $fechaInicio = $residencia->fechaInicio;
                $carrera = $residencia->carrera;

                $sentencia->execute();

                // Retornar en el último id insertado
                return $pdo->lastInsertId();

            } catch (PDOException $e) {
                throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
            }
        } else {
            throw new ExcepcionApi(
                self::ESTADO_ERROR_PARAMETROS,
                utf8_encode("Error en existencia o sintaxis de par�metros"));
        }

    }


    public static function put($peticion)
    {
        $idUsuario = usuarios::autorizar();

        if (!empty($peticion[0])) {
            $body = file_get_contents('php://input');
            $residencia = json_decode($body);

            if (self::actualizar($idUsuario, $residencia, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro actualizado correctamente"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "El contacto al que intentas acceder no existe", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }
    }


      /**
     * Actualiza el contacto especificado por idUsuario
     * @param int $idUsuario
     * @param object $residencia objeto con los valores nuevos del contacto
     * @param int $idResidencia
     * @return PDOStatement
     * @throws Exception
     */
    private static function actualizar($idUsuario, $residencia, $idResidencia)
    {
        try {
            // Creando consulta UPDATE
            $consulta = "UPDATE " . self::NOMBRE_TABLA .
                " SET " . self::NOMBRE_RESIDENCIA . "=?," .
                self::NUM_INTEGRANTES . "=?," .
                self::FECHA_INICIO . "=?," .
                self::CARRERA . "=? " .
                " WHERE " . self::ID_RESIDENCIA . "=? AND " . self::ID_USUARIO . "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($consulta);

            $sentencia->bindParam(1, $nombreResidencia);
            $sentencia->bindParam(2, $numIntegrantes);
            $sentencia->bindParam(3, $fechaInicio);
            $sentencia->bindParam(4, $carrera);
            $sentencia->bindParam(5, $idResidencia);
            $sentencia->bindParam(6, $idUsuario);

            $nombreResidencia = $residencia->nombreResidencia;
            $numIntegrantes = $residencia->numIntegrantes;
            $fechaInicio = $residencia->fechaInicio;
            $carrera = $residencia->carrera;

            // Ejecutar la sentencia
            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }


    public static function delete($peticion)
    {
        $idUsuario = usuarios::autorizar();

        if (!empty($peticion[0])) {
            if (self::eliminar($idUsuario, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro eliminado correctamente"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "La Residencia a la que intentas acceder no existe", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }

    }

    /**
     * Elimina un contacto asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param int $idResidencias identificador del contacto
     * @return bool true si la eliminación se pudo realizar, en caso contrario false
     * @throws Exception excepcion por errores en la base de datos
     */
    private static function eliminar($idUsuario, $idResidencia)
    {
        try {
            // Sentencia DELETE
            $comando = "DELETE FROM " . self::NOMBRE_TABLA .
                " WHERE " . self::ID_RESIDENCIA . "=? AND " .
                self::ID_USUARIO . "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            $sentencia->bindParam(1, $idResidencia);
            $sentencia->bindParam(2, $idUsuario);

            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

}




