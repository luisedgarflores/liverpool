<?php

require 'database.php';
$f_idError = null;
$cantidadError = null;
$unidadError = null;
$productoError = null;
$marcaError = null;
$precioVentaError = null;
$departamentoError = null;	
$id = null;
if (!empty($_GET['id'])) {
	$id = $_REQUEST['id'];
}

if (null == $id) { //En caso de que no se haya mandado nada se regresa al index
	header("Location: productos.php");
}

if (!empty($_POST)) {
	// keep track validation errors
	// keep track post values
	$f_id = $_POST['f_id'];
	$producto = $_POST['producto'];
	$marca = $_POST['marca'];
	$precioVenta = $_POST['precioVenta'];
	$cantidad = $_POST['cantidad'];
	$unidad = $_POST['unidad'];
	$departamento = $_POST['departamento'];
	/// validate input
	$valid = true;

	if (empty($producto)) {
		$productoError = 'Porfavor escriba un nombre';
		$valid = false;
	}

	if (empty($marca)) {
		$marcaError = 'Porfavor seleccione una marca';
		$valid = false;
	}

	if (empty($departamento)) {
		$departamentoError = 'Porfavor seleccione un departamento';
		$valid = false;
	}

	if (empty($cantidad) or $cantidad < 0) {
		$departamentoError = 'Porfavor introduzca una cantidad entera';
		$valid = false;
	}

	if (empty($precioVenta) or $precioVenta < 0) {
		$precioVentaError = 'Porfavor introduzca un precio entero';
		$valid = false;
	}

	if (empty($unidad)) {
		$unidadError = 'Porfavor seleccione una unidad';
		$valid = false;
	}

	// update data
	if ($valid) {

		$pdo = Database::connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql = "UPDATE inventario set idInventario = ?, cantidadVendible =?, idUnidadVendible =?, nombreProducto = ?, idDepartamento= ?, idMarca = ?, precioVenta= ? WHERE idInventario = ?";
		$pdo->beginTransaction();
		try {
			$q = $pdo->prepare($sql);
			$q->execute(array($f_id, $cantidad, $unidad, $producto, $departamento, $marca, $precioVenta, $id));
			$pdo->commit();
		}
		catch(Exception $e){
			echo('No se pudo concretar la operacion, intente mas tarde') ;
			$pdo->rollback();
		}


		Database::disconnect();
		header("Location: productos.php");
	}
} else {
	$pdo = Database::connect();
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = "SELECT 
	 idInventario,
	 nombreProducto as producto,
	 cantidadVendible as cantidad,
	 idDepartamento as departamento,
	 idMarca as marca,
	 idUnidad as unidad,
	 precioVenta
	 FROM inventario 
	 NATURAL JOIN departamento
	 NATURAL JOIN marca
	 INNER JOIN unidad
     ON inventario.idUnidadVendible=unidad.idUnidad 
	 where idInventario = ?";

	$q = $pdo->prepare($sql);
	$q->execute(array($id)); //SE guarda un arreglo con la info del auto
	$data = $q->fetch(PDO::FETCH_ASSOC);
	$f_id = $data['idInventario'];
	$producto = $data['producto']; //Se asignan los valores que tenía el auto
	$marca = $data['marca'];
	$departamento = $data['departamento'];
	$cantidad = $data['cantidad'];
	$precioVenta = $data['precioVenta'];
	$unidad = $data['unidad'];
	Database::disconnect();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<script src="js/bootstrap.min.js"></script>
</head>

<body>
	<div class="container">
		<div class="span10 offset1">
			<div class="row">
				<h3>Actualizar producto</h3>
			</div>

			<form class="form-horizontal" action="updateProducto.php?id=<?php echo $id ?>" method="post">

				<div class="control-group <?php echo !empty($f_idError) ? 'error' : ''; ?>">

					<label class="control-label">id</label>
					<div class="controls">
						<input name="f_id" type="text" readonly placeholder="id" value="<?php echo !empty($id) ? $id : ''; ?>">
						<?php if (!empty($f_idError)) : ?>
							<span class="help-inline"><?php echo $f_idError; ?></span>
						<?php endif; ?>
					</div>
				</div>

				<div class="control-group <?php echo !empty($productoError) ? 'error' : ''; ?>">

					<label class="control-label">Nombre</label>
					<div class="controls">
						<input name="producto" type="text" placeholder="Nombre" value="<?php echo !empty($producto) ? $producto : ''; ?>">
						<?php if (!empty($producto)) : ?>
							<span class="help-inline"><?php echo $productoError; ?></span>
						<?php endif; ?>
					</div>
				</div>

				<div class="control-group <?php echo !empty($marcaError) ? 'error' : ''; ?>">
					<label class="control-label">marca</label>
					<div class="controls">
						<select name="marca">
							<option value="">Selecciona una marca</option>
							<?php
							$pdo = Database::connect();
							$query = 'SELECT * FROM marca';
							foreach ($pdo->query($query) as $row) {
								if ($row['idMarca'] == $marca)
									echo "<option selected value='" . $row['idMarca'] . "'>" . $row['nombreMarca'] . "</option>";
								else
									echo "<option value='" . $row['idMarca'] . "'>" . $row['nombreMarca'] . "</option>";
							}
							Database::disconnect();
							?>

						</select>
						<?php if (!empty($marcaError)) : ?>
							<span class="help-inline"><?php echo $marcaError; ?></span>
						<?php endif; ?>
					</div>
				</div>

				<div class="control-group <?php echo !empty($departamentoError) ? 'error' : ''; ?>">
					<label class="control-label">Departamento</label>
					<div class="controls">
						<select name="departamento">
							<option value="">Selecciona un departamento</option>
							<?php
							$pdo = Database::connect();
							$query = 'SELECT * FROM departamento';
							foreach ($pdo->query($query) as $row) {
								if ($row['idDepartamento'] == $departamento)
									echo "<option selected value='" . $row['idDepartamento'] . "'>" . $row['nombreDepartamento'] . "</option>";
								else
									echo "<option value='" . $row['idDepartamento'] . "'>" . $row['nombreDepartamento'] . "</option>";
							}
							Database::disconnect();
							?>

						</select>
						<?php if (!empty($departamentoError)) : ?>
							<span class="help-inline"><?php echo $departamentoError; ?></span>
						<?php endif; ?>
					</div>
				</div>

				<div class="control-group <?php echo !empty($cantidadError) ? 'error' : ''; ?>">

					<label class="control-label">Cantidad</label>
					<div class="controls">
						<input name="cantidad" type="number" placeholder="Cantidad" value="<?php echo !empty($cantidad) ? $cantidad : ''; ?>">
						<?php if (!empty($cantidad)) : ?>
							<span class="help-inline"><?php echo $cantidadError; ?></span>
						<?php endif; ?>
					</div>
				</div>

				<div class="control-group <?php echo !empty($unidadError) ? 'error' : ''; ?>">
					<label class="control-label">Unidad</label>
					<div class="controls">
						<select name="unidad">
							<option value="">Selecciona un unidad</option>
							<?php
							$pdo = Database::connect();
							$query = 'SELECT * FROM unidad';
							foreach ($pdo->query($query) as $row) {
								if ($row['idUnidad'] == $unidad)
									echo "<option selected value='" . $row['idUnidad'] . "'>" . $row['nombreUnidad'] . "</option>";
								else
									echo "<option value='" . $row['idUnidad'] . "'>" . $row['nombreUnidad'] . "</option>";
							}
							Database::disconnect();
							?>

						</select>
						<?php if (!empty($unidadError)) : ?>
							<span class="help-inline"><?php echo $unidadError; ?></span>
						<?php endif; ?>
					</div>
				</div>

				<div class="control-group <?php echo !empty($precioVentaError) ? 'error' : ''; ?>">

					<label class="control-label">Precio venta</label>
					<div class="controls">
						<input name="precioVenta" type="number" placeholder="Precio venta" value="<?php echo !empty($precioVenta) ? $precioVenta : ''; ?>">
						<?php if (!empty($precioVenta)) : ?>
							<span class="help-inline"><?php echo $precioVentaError; ?></span>
						<?php endif; ?>
					</div>
				</div>

				<div class="form-actions">
					<button type="submit" class="btn btn-success">Actualizar</button>
					<a class="btn" href="index.php">Regresar</a>
				</div>
			</form>
		</div>

	</div> <!-- /container -->
</body>

</html>