<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<script src="js/bootstrap.min.js"></script>
</head>

<body>
	<div class="container">
		<div class="row">
			<nav class="navbar navbar-default">
				<div class="container-fluid">
					<ul class="nav navbar-nav">
						<li class="active"><a href="index.php">Ventas</a></li>
						<li><a href="productos.php">Inventario</a></li>
					</ul>
				</div>
			</nav>
		</div>
		<hr>
		<div class="row">
			<div class="row">
				<p>Realizar una nueva venta</p>
			</div>
			<div class="row">
				<a class="btn btn-primary" href="create.php?departamento='Linea blanca'">Linea blanca</a>
				<a class="btn btn-primary" href="create.php?departamento='Electronicos'">Electronicos</a>
				<a class="btn btn-primary" href="create.php?departamento='Telefonia'">Telefonia</a>
				<a class="btn btn-primary" href="create.php?departamento='Muebles'">Muebles</a>
			</div>
		</div>
		<hr>
		<div class="row">
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Producto</th>
						<th>Cantidad</th>
						<th>Vendedor</th>
						<th>Caja</th>
						<th>Cliente</th>
						<th>Metodo de pago</th>
						<th>Total</th>
					</tr>
				</thead>
				<tbody>
					<?php
					include 'database.php';
					$pdo = Database::connect();
					$sql = 'SELECT 
						idVenta,
						total,
						cantidad,
						nombreCliente as cliente, 
						nombreCajaCobro as caja,
						nombreEmpleado as empleado,
						nombreMetodoPago as pago,
						nombreProducto as producto
						FROM venta 
						natural join cajacobro 
						natural join cliente 
						natural join empleado
						natural join metodopago
						natural join inventario
					 	LIMIT 20';
					foreach ($pdo->query($sql) as $row) {
						echo '<tr>';
						echo '<td>' . $row['producto'] . '</td>';
						echo '<td>' . $row['cantidad'] . '</td>';
						echo '<td>' . $row['empleado'] . '</td>';
						echo '<td>' . $row['caja'] . '</td>';
						echo '<td>' . $row['cliente'] . '</td>';
						echo '<td>' . $row['pago'] . '</td>';
						echo '<td>' . $row['total'] . '</td>';
					}
					Database::disconnect();
					?>
				</tbody>
			</table>

		</div>
	</div> <!-- /container -->
</body>

</html>