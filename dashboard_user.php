<?php 
session_start();
include("conexion.php");

if(!isset($_SESSION['id']) || $_SESSION['rol'] !== 'cliente'){
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id'];

/* =========================
   BUSCADOR
========================= */
$search = $_GET['search'] ?? '';

/* =========================
   FILTROS
========================= */
$filtro = $_GET['filtro'] ?? 'todos';
$cat = $_GET['cat'] ?? null;

$where = "WHERE 1=1";

if($search != ""){
    $where .= " AND p.nombre LIKE '%$search%'";
}

if($filtro == "disponibles"){
    $where .= " AND p.stock > 0";
}

if($filtro == "nodisponibles"){
    $where .= " AND p.stock = 0";
}

if($cat){
    $where .= " AND p.id_categoria = '$cat'";
}

/* =========================
   FAVORITOS DELETE
========================= */
if(isset($_GET['delete'])){
    $id_favorito = $_GET['delete'];

    mysqli_query($conn, "
        DELETE FROM Favorito 
        WHERE id_favorito='$id_favorito'
        AND id_usuario='$id_usuario'
    ");

    header("Location: dashboard_user.php");
    exit();
}

/* =========================
   FAVORITOS ADD
========================= */
if(isset($_POST['add_fav'])){
    $id_producto = $_POST['id_producto'];

    $check = mysqli_query($conn, "
        SELECT * FROM Favorito 
        WHERE id_usuario='$id_usuario' 
        AND id_producto='$id_producto'
    ");

    if(mysqli_num_rows($check) == 0){
        mysqli_query($conn, "
            INSERT INTO Favorito (id_usuario, id_producto, fecha)
            VALUES ('$id_usuario', '$id_producto', NOW())
        ");
    }
}

/* =========================
   CATEGORÍAS
========================= */
$categorias = mysqli_query($conn,"SELECT * FROM Categoria");

/* =========================
   PAGINACIÓN
========================= */
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

/* =========================
   PRODUCTOS (SQL CORREGIDO PARA IMÁGENES MÚLTIPLES)
========================= */
$productos = mysqli_query($conn, "
    SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.stock, 
           p.imagen AS imagen_principal, 
           (SELECT pi.imagen FROM producto_imagen pi WHERE pi.id_producto = p.id_producto LIMIT 1) AS imagen_galeria,
           c.nombre_categoria
    FROM Producto p
    INNER JOIN Categoria c ON p.id_categoria = c.id_categoria
    $where
    LIMIT $start, $limit
");

/* TOTAL */
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM Producto p $where");
$total_data = mysqli_fetch_assoc($total_query);
$total = $total_data['total'];
$pages = ceil($total / $limit);

/* =========================
   FAVORITOS LIST (SQL CORREGIDO)
========================= */
$favoritos = mysqli_query($conn, "
    SELECT f.id_favorito, p.id_producto, p.nombre, p.precio, p.stock,
           p.imagen AS imagen_principal,
           (SELECT pi.imagen FROM producto_imagen pi WHERE pi.id_producto = p.id_producto LIMIT 1) AS imagen_galeria
    FROM Favorito f
    INNER JOIN Producto p ON f.id_producto = p.id_producto
    WHERE f.id_usuario='$id_usuario'
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gym Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .sticky-nav { position: sticky; top: 0; z-index: 1000; }
        footer { background:#111; color:white; padding:30px; margin-top:50px; text-align:center; }
        .product-img { height: 200px; width: 100%; object-fit: cover; }
        .fav-img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
    </style>
</head>

<body class="bg-light">

<div class="sticky-nav">
    <?php include("navbar_user.php"); ?>
</div>

<div class="container mt-4">

    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a href="?filtro=todos" class="btn btn-dark btn-sm">Todos</a>
        <a href="?filtro=disponibles" class="btn btn-success btn-sm">Disponibles</a>
        <a href="?filtro=nodisponibles" class="btn btn-secondary btn-sm">No disponibles</a>
    </div>

    <div class="mb-4">
        <h5>Categorías</h5>
        <div class="d-flex gap-2 flex-wrap">
            <?php while($c = mysqli_fetch_assoc($categorias)){ ?>
                <a href="?cat=<?php echo $c['id_categoria']; ?>" class="btn btn-outline-primary btn-sm">
                    <?php echo $c['nombre_categoria']; ?>
                </a>
            <?php } ?>
        </div>
    </div>

    <hr>

    <div class="d-flex justify-content-between mb-3">
        <h4>Productos</h4>
        <a href="producto_create.php" class="btn btn-success">➕ Agregar producto</a>
    </div>

    <div class="row">
        <?php while($p = mysqli_fetch_assoc($productos)){ 
            // Lógica para seleccionar la imagen a mostrar
            $img_name = "default.png";
            if(!empty($p['imagen_principal'])){
                $img_name = $p['imagen_principal'];
            } elseif(!empty($p['imagen_galeria'])){
                $img_name = $p['imagen_galeria'];
            }
            $path = "uploads/" . $img_name;
        ?>

        <div class="col-md-4 mb-3">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <img src="<?php echo $path; ?>" class="product-img mb-3" alt="Producto">

                    <h5><?php echo $p['nombre']; ?></h5>
                    <p class="text-muted small"><?php echo $p['nombre_categoria']; ?></p>
                    <p class="fw-bold text-primary">$<?php echo $p['precio']; ?></p>
                    <p class="small">Stock: <?php echo $p['stock']; ?></p>

                    <div class="d-grid gap-2">
                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal<?php echo $p['id_producto']; ?>">
                            Ver detalles
                        </button>

                        <div class="d-flex gap-1 justify-content-center">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="id_producto" value="<?php echo $p['id_producto']; ?>">
                                <button name="add_fav" class="btn btn-warning btn-sm">★</button>
                            </form>

                            <?php if($p['stock'] > 0){ ?>
                                <form method="POST" action="carrito_add.php" class="d-inline d-flex gap-1">
                                    <input type="hidden" name="id_producto" value="<?php echo $p['id_producto']; ?>">
                                    <input type="number" name="cantidad" value="1" min="1" max="<?php echo $p['stock']; ?>" class="form-control form-control-sm w-50">
                                    <button class="btn btn-success btn-sm">🛒</button>
                                </form>
                            <?php } else { ?>
                                <button class="btn btn-secondary btn-sm" disabled>Sin stock</button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal<?php echo $p['id_producto']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo $p['nombre']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="<?php echo $path; ?>" class="img-fluid rounded mb-3">
                        <div class="text-start">
                            <p><b>Categoría:</b> <?php echo $p['nombre_categoria']; ?></p>
                            <p><b>Descripción:</b> <?php echo $p['descripcion']; ?></p>
                            <p><b>Precio:</b> $<?php echo $p['precio']; ?></p>
                            <p><b>Stock actual:</b> <?php echo $p['stock']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for($i = 1; $i <= $pages; $i++){ ?>
                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&filtro=<?php echo $filtro; ?>&cat=<?php echo $cat; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </nav>

    <hr class="my-5">

    <h4>Mis Favoritos</h4>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while($f = mysqli_fetch_assoc($favoritos)){ 
                    $img_fav = "default.png";
                    if(!empty($f['imagen_principal'])){
                        $img_fav = $f['imagen_principal'];
                    } elseif(!empty($f['imagen_galeria'])){
                        $img_fav = $f['imagen_galeria'];
                    }
                ?>
                <tr>
                    <td><img src="uploads/<?php echo $img_fav; ?>" class="fav-img"></td>
                    <td><?php echo $f['nombre']; ?></td>
                    <td>$<?php echo $f['precio']; ?></td>
                    <td>
                        <?php echo ($f['stock'] > 0) ? '<span class="badge bg-success">Disponible</span>' : '<span class="badge bg-danger">Agotado</span>'; ?>
                    </td>
                    <td>
                        <?php if($f['stock'] > 0){ ?>
                            <form method="POST" action="carrito_add.php" class="d-inline">
                                <input type="hidden" name="id_producto" value="<?php echo $f['id_producto']; ?>">
                                <input type="hidden" name="cantidad" value="1">
                                <button class="btn btn-success btn-sm">🛒 Comprar</button>
                            </form>
                        <?php } ?>
                        <a href="?delete=<?php echo $f['id_favorito']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Quitar de favoritos?')">Eliminar</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div> <footer>
    <h5>Gym Store</h5>
    <p>Sistema de ventas - PHP & MySQL</p>
    <small>© 2026</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>