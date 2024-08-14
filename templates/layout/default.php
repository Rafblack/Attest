<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <title><?= isset($title) ? h($title) : 'Attest' ?></title>
    <?= $this->Html->meta('viewport', 'width=device-width, initial-scale=1') ?>
    <?= $this->Html->css('https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css') ?>
    <?= $this->Html->css('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap') ?>
    <?= $this->Html->css('styles.css') ?>
    <?= $this->Html->script('https://code.jquery.com/jquery-3.5.1.slim.min.js') ?>
    <?= $this->Html->script('https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js') ?>
    <?= $this->Html->script('https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js') ?>
    <?= $this->Html->css('cake') ?> 

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>

</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-0">
                <li class="nav-item active">
                    <a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Dropdown
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="/attestations/add">Ajouter</a>
                        <a class="dropdown-item" href="/Attestations/deleted">Attestation désactivées</a>
                    </div>
                </li>
            </ul>
        </div>
        <form class="form-inline my-2 my-lg-0"> 
            <a class="navbar-brand d-flex align-items-center" href="/">
                <?= $this->Html->image('attestation.jpg', ['alt' => 'Attestation Logo', 'style' => 'height: 60px; margin-right: 10px;']) ?>
            </a>
        </form> 
    </nav>

    <main role="main" class="container">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </main>

    <footer class="footer">
        <div class="container">
            <?= $this->Html->image('footer.jpg', ['alt' => 'Footer Logo']) ?>
        </div>
    </footer>
</body>
</html>
