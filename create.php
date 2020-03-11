<?php

require 'database.php';

$metodoError = null;
$empleadoError = null;
$productoError = null;
$clienteError = null;
$cantidadError = null;
$cajaError = null;
$cantidadSuperada = null;
$departamento = null;
if (!empty($_GET['departamento'])) {
	$departamento = $_REQUEST['departamento'];
}

if (null == $departamento) { //En caso de que no se haya mandado nada se regresa al index
	header("Location: index.php");
}

//$perError = null;

if (!empty($_POST)) { //Se revisa si ya se había accedido anteriormente a la página

	// keep track post values		

	$producto = $_POST['producto'];
	$cliente = $_POST['cliente'];
	$empleado = $_POST['empleado'];
	$total = 0;
	$cantidad = $_POST['cantidad'];
	$metodo = $_POST['metodo'];
	$caja = $_POST['caja'];

	// validate input
	$valid = true; // BOolean usado para las comprobaciones

	if (empty($producto)) { //Si no se puso nada en id marca
		$productoError = 'Porfavor seleccione un producto';
		$valid = false;
	}
	if (empty($caja)) { //Si no se puso nada en id marca
		$cajaError = 'Porfavor seleccione una caja';
		$valid = false;
	}
	if (empty($cliente)) { //En caso de que no se haya específicado si tiene o no aire acondicionado
		$clienteError = 'Porfavor seleccione un cliente';
		$valid = false;
	}

	if (empty($empleado)) { //En caso de que no se haya específicado si tiene o no aire acondicionado
		$empleadoError = 'Porfavor seleccione un empleado';
		$valid = false;
	}

	if (empty($metodo)) { //En caso de que no se haya específicado si tiene o no aire acondicionado
		$metodoError = 'Porfavor seleccione un metodo de pago';
		$valid = false;
	}
	if (empty($cantidad) or $cantidad <= 0) { //En caso de que no se haya específicado si tiene o no aire acondicionado
		$cantidadError = 'Porfavor introduzca una cantidad mayor a 0';
		$valid = false;
	}

	// insert data
	if ($valid) { //En caso de que se cumplan todas las condiciones
		var_dump($_POST);
		$pdo = Database::connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$getProduct = "SELECT * FROM inventario WHERE idInventario = ? LIMIT 1";
		$updateInventory = "UPDATE inventario SET cantidadVendible = ? WHERE idInventario = ?";
		$sql = "INSERT INTO venta (
			idVenta,
			idInventario,
			cantidad,
			idMetodoPago, 
			idEmpleado,
			idCajaCobro,
			idCliente,
			total) 
			values(null,?, ?,?,?,?,?,?)";
		$pdo->beginTransaction();
		try {
			$stmt = $pdo->prepare($getProduct);
			$stmt->execute(array($producto));
			$productData = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$productData['ProductInfo'][] = $row;
			}
			$nuevaCantidad = $productData['ProductInfo'][0]['cantidadVendible'] - $cantidad;
			if ($nuevaCantidad >= 0) {
				$total = $productData['ProductInfo'][0]['precioVenta'] * $cantidad;
				$x = $pdo->prepare($updateInventory);
				$x->execute(array($nuevaCantidad, $producto));
				$q = $pdo->prepare($sql);
				$q->execute(array($producto, $cantidad, $metodo, $empleado, $caja, $cliente, $total));
				$pdo->commit();	// ejecuta la consulta en la base	
				Database::disconnect();
				header("Location: index.php");
			} else {
				$cantidadSuperada = 'No se tiene esa cantidad de producto';
                $pdo->rollback();
			}
		} catch (Exception $e) {
			$pdo->rollback();
		}
	}
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
				<h3>Registro de ventas</h3>
			</div>

			<form class="form-horizontal" action="create.php?departamento=<?php echo $departamento ?>"" method="post">
				<div class="control-group <?php echo !empty($productoError) ? 'error' : ''; ?>">
					<label class="control-label">Producto</label>
					<div class="controls">
						<select name="producto">
							<option value="">Selecciona un producto</option>
							<!--SE deja su valor como vacío para que se detecte que no se ha seleccionado ninguna variable-->
							<?php
							$pdo = Database::connect();
							$query = "SELECT * FROM inventario NATURAL JOIN departamento WHERE nombreDepartamento = $departamento";
							foreach ($pdo->query($query) as $row) {
								if ($row['idInventario'] == $producto) // Se usa para volver a colocar el valor de la marca seleccionada previamente en la página en caso de que ya se haya guardado algo en POST, para que el usuario no la tenga que volver a meter
									echo "<option selected value='" . $row['idInventario'] . "'>" . $row['nombreProducto'] . " $" . $row['precioVenta'] . "</option>";
								else
									echo "<option value='" . $row['idInventario'] . "'>" . $row['nombreProducto'] . " $" . $row['precioVenta'] . "</option>"; //En esta parte se muestran las marcas registradas en la base de datos
							}
							Database::disconnect();
							?>
						</select>
						<?php if (($productoError) != null) ?>
					</div>
				</div>
				<div class="control-group <?php echo !empty($cantidadError) ? 'error' : ''; ?>">
					<!--En caso de que haya error en sumbarca se muestra-->
					<label class="control-label">Cantidad</label>
					<div class="controls">
						<input id="cantidad" name="cantidad" type="text" placeholder="Cantidad" value="<?php echo !empty($cantidad) ? $cantidad : ''; ?>">
						<!--En caso de que ya se haya guardado un valor previamente en la página y pasó un error, se jala el valor previamente guardado para que el usuario no tenga que llenar todo de nuevo-->
						<?php if (($cantidadError != null)) ?>
						<span class="help-inline"><?php echo $cantidadError; ?></span>
					</div>
				</div>
				<div class="control-group <?php echo !empty($metodoError) ? 'error' : ''; ?>">
					<label class="control-label">Metodo de pago</label>
					<div class="controls">
						<select name="metodo">
							<option value="">Selecciona un metodo</option>
							<!--SE deja su valor como vacío para que se detecte que no se ha seleccionado ninguna variable-->
							<?php
							$pdo = Database::connect();
							$query = 'SELECT * FROM metodopago';
							foreach ($pdo->query($query) as $row) {
								if ($row['idMetodoPago'] == $metodo) // Se usa para volver a colocar el valor de la marca seleccionada previamente en la página en caso de que ya se haya guardado algo en POST, para que el usuario no la tenga que volver a meter
									echo "<option selected value='" . $row['idMetodoPago'] . "'>" . $row['nombreMetodoPago'] . "</option>";
								else
									echo "<option value='" . $row['idMetodoPago'] . "'>" . $row['nombreMetodoPago'] . "</option>"; //En esta parte se muestran las marcas registradas en la base de datos
							}
							Database::disconnect();
							?>
						</select>
						<?php if (($metodoError) != null) ?>
					</div>
				</div>
				<div class="control-group <?php echo !empty($empleadoError) ? 'error' : ''; ?>">
					<label class="control-label">Empleado</label>
					<div class="controls">
						<select name="empleado">
							<option value="">Selecciona un empleado</option>
							<!--SE deja su valor como vacío para que se detecte que no se ha seleccionado ninguna variable-->
							<?php
							$pdo = Database::connect();
							$query = 'SELECT * FROM empleado';
							foreach ($pdo->query($query) as $row) {
								if ($row['idEmpleado'] == $empleado) // Se usa para volver a colocar el valor de la marca seleccionada previamente en la página en caso de que ya se haya guardado algo en POST, para que el usuario no la tenga que volver a meter
									echo "<option selected value='" . $row['idEmpleado'] . "'>" . $row['nombreEmpleado'] . "</option>";
								else
									echo "<option value='" . $row['idEmpleado'] . "'>" . $row['nombreEmpleado'] . "</option>"; //En esta parte se muestran las marcas registradas en la base de datos
							}
							Database::disconnect();
							?>
						</select>
						<?php if (($empleadoError) != null) ?>
					</div>
				</div>
				<div class="control-group <?php echo !empty($cajaError) ? 'error' : ''; ?>">
					<label class="control-label">Caja de cobro</label>
					<div class="controls">
						<select name="caja">
							<option value="">Selecciona una caja</option>
							<!--SE deja su valor como vacío para que se detecte que no se ha seleccionado ninguna variable-->
							<?php
							$pdo = Database::connect();
							$query = 'SELECT * FROM cajacobro';
							foreach ($pdo->query($query) as $row) {
								if ($row['idCajaCobro'] == $caja) // Se usa para volver a colocar el valor de la marca seleccionada previamente en la página en caso de que ya se haya guardado algo en POST, para que el usuario no la tenga que volver a meter
									echo "<option selected value='" . $row['idCajaCobro'] . "'>" . $row['nombreCajaCobro'] . "</option>";
								else
									echo "<option value='" . $row['idCajaCobro'] . "'>" . $row['nombreCajaCobro'] . "</option>"; //En esta parte se muestran las marcas registradas en la base de datos
							}
							Database::disconnect();
							?>
						</select>
						<?php if (($cajaError) != null) ?>
					</div>
				</div>
				<div class="control-group <?php echo !empty($clienteError) ? 'error' : ''; ?>">
					<label class="control-label">Cliente</label>
					<div class="controls">
						<select name="cliente">
							<option value="">Selecciona un cliente</option>
							<!--SE deja su valor como vacío para que se detecte que no se ha seleccionado ninguna variable-->
							<?php
							$pdo = Database::connect();
							$query = 'SELECT * FROM cliente';
							foreach ($pdo->query($query) as $row) {
								if ($row['idCliente'] == $cliente) // Se usa para volver a colocar el valor de la marca seleccionada previamente en la página en caso de que ya se haya guardado algo en POST, para que el usuario no la tenga que volver a meter
									echo "<option selected value='" . $row['idCliente'] . "'>" . $row['nombreCliente'] . "</option>";
								else
									echo "<option value='" . $row['idCliente'] . "'>" . $row['nombreCliente'] . "</option>"; //En esta parte se muestran las marcas registradas en la base de datos
							}
							Database::disconnect();
							?>
						</select>
						<?php if (($clienteError) != null) ?>
					</div>
				</div>
				<?php echo !empty($cantidadSuperada) ? "<p color='red'>No se cuenta con esa cantidad en inventario</p>" : ''; ?>
				<div class="form-actions">
                    <button type="submit" class="btn btn-success">Agregar</button>
					<a class="btn" href="index.php">Regresar</a>
				</div>

			</form>
		</div>
	</div> <!-- /container -->
</body>

</html>