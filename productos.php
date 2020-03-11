<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<script src="js/bootstrap.min.js"></script>
</head>

<body>
	<div class="container-fluid">
		<div class="row">
			<nav class="navbar navbar-default">
				<div class="container-fluid">
					<ul class="nav navbar-nav">
						<li><a href="index.php">Ventas</a></li>
						<li class="active"><a href="productos.php">Inventario</a></li>
					</ul>
				</div>
			</nav>
		</div>
		<div class="row">
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Producto</th>
                        <th>Disponibles</th>
						<th>Precio</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					include 'database.php';
					$pdo = Database::connect();
					$sql = 'SELECT 
						idInventario,
						cantidadVendible,
						precioVenta,
						nombreProducto as producto, 
                        nombreUnidad as unidad
						FROM inventario 
                        INNER JOIN unidad
                        On inventario.idUnidadVendible=unidad.idUnidad 
					 	ORDER BY precioVenta';
					foreach ($pdo->query($sql) as $row) {
						echo '<tr>';
						echo '<td>' . $row['producto'] . '</td>';
                        echo '<td>' . $row['cantidadVendible'] . ' ' . $row['unidad'] . '</td>';
						echo '<td>' . $row['precioVenta'] . '</td>';
						echo '<td>';
						echo '&nbsp;';
						echo '<a class="btn btn-success" href="updateProducto.php?id=' . $row['idInventario'] . '">Actualizar</a>';
						echo '</td>';
						echo '</tr>';
					}
					Database::disconnect();
					?>
				</tbody>
			</table>

		</div>
	</div> <!-- /container -->
</body>

</html>