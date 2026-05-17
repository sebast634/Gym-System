<?php
include("conexion.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* valores actuales para no perder filtros */
$search = $_GET['search'] ?? '';
$filtro = $_GET['filtro'] ?? 'todos';
$orden  = $_GET['orden'] ?? '';
$cat    = $_GET['cat'] ?? '';

$categorias = mysqli_query($conn, "SELECT * FROM Categoria");
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3 sticky-top">

<!-- LOGO -->
<a class="navbar-brand fw-bold" href="dashboard_user.php">
🛍 GymStore
</a>

<!-- SEARCH -->
<form class="d-flex mx-3 flex-grow-1" method="GET" action="dashboard_user.php">

<input type="hidden" name="filtro" value="<?php echo $filtro; ?>">
<input type="hidden" name="orden" value="<?php echo $orden; ?>">
<input type="hidden" name="cat" value="<?php echo $cat; ?>">

<input class="form-control me-2"
type="search"
name="search"
placeholder="Buscar productos..."
value="<?php echo htmlspecialchars($search); ?>">

<button class="btn btn-warning" type="submit">🔍</button>

</form>

<!-- CATEGORÍAS -->
<div class="dropdown me-2">

<button class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
Categorías
</button>

<ul class="dropdown-menu">

<li>
<a class="dropdown-item"
href="dashboard_user.php?search=<?php echo urlencode($search); ?>&filtro=<?php echo $filtro; ?>&orden=<?php echo $orden; ?>">
Todas
</a>
</li>

<?php while($c = mysqli_fetch_assoc($categorias)){ ?>

<li>
<a class="dropdown-item"
href="dashboard_user.php?cat=<?php echo $c['id_categoria']; ?>
&search=<?php echo urlencode($search); ?>
&filtro=<?php echo $filtro; ?>
&orden=<?php echo $orden; ?>">
<?php echo $c['nombre_categoria']; ?>
</a>
</li>

<?php } ?>

</ul>

</div>

<!-- CARRITO -->
<a href="carrito.php" class="btn btn-success me-2">
🛒 Carrito
</a>

<!-- HISTORIAL -->
<a href="historial_compras.php" class="btn btn-info me-2">
📦 Compras
</a>

<!-- USUARIO -->
<div class="dropdown">

<button class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
👤 <?php echo $_SESSION['nombre']; ?>
</button>

<ul class="dropdown-menu dropdown-menu-end">

<li>
<a class="dropdown-item" href="dashboard_user.php">Inicio</a>
</li>

<li>
<a class="dropdown-item text-danger" href="logout.php">Cerrar sesión</a>
</li>

</ul>

</div>

</nav>