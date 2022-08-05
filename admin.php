<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="./css/style.css" />
    <title>Admin</title>
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <p>Logo</p>
            </div>
            <nav>
                <ul class="profil">
                    <li><a href="logout.php">Logout</a></li>
                    <li><a href="missing.php">Missing</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <section>
            <div>
                <div>
                    <img src="./assets/images/card.png" alt="profil">
                </div>
                <div>
                    <p>Pseudo: <span id="pseudo"></span></p>
                    <p>Full Name: <span id="name"></span></p>
                    <p>Phone Number: <span id="phone"></span></p>
                    <p>Email: <span id="mail"></span></p>
                </div>
            </div>
            <div>
                <div><button>Update</button><button>Delete</button></div>
            </div>
            <div id="label">
                <div>
                    <h1>Missing Persons ...</h1>
                </div>
                <article>
                    <div>
                        <h2>ID : 456988752102563 --- John Doe</h2>
                    </div>
                    <div>
                        <form action="post">
                            <button type="submit">
                                <img src="./assets/images/isActive.png" alt="isActive">
                            </button>
                        </form>
                        <form action="post">
                            <button type="submit">
                                <img src="./assets/images/Update.png" alt="Update">
                            </button>
                        </form>
                        <form action="post">
                            <button type="submit">
                                <img src="./assets/images/Delete.png" alt="Delete">
                            </button>
                        </form>
                    </div>
                </article>
                <article>
                    <div>
                        <h2>ID : 456988752102563 --- John Doe</h2>
                    </div>
                    <div>
                        <form action="post">
                            <button type="submit">
                                <img src="./assets/images/isActive.png" alt="isActive">
                            </button>
                        </form>
                        <form action="post">
                            <button type="submit">
                                <img src="./assets/images/Update.png" alt="Update">
                            </button>
                        </form>
                        <form action="post">
                            <button type="submit">
                                <img src="./assets/images/Delete.png" alt="Delete">
                            </button>
                        </form>
                    </div>
                </article>
                <article>
                    <div>
                        <h2>ID : 456988752102563 --- John Doe</h2>
                    </div>
                    <div>
                        <form action="post">
                            <button type="submit">
                                <img src="./assets/images/isActive.png" alt="isActive">
                            </button>
                        </form>
                        <form action="post">
                            <button type="submit">
                                <img src="./assets/images/Update.png" alt="Update">
                            </button>
                        </form>
                        <form action="post">
                            <button type="submit">
                                <img src="./assets/images/Delete.png" alt="Delete">
                            </button>
                        </form>
                    </div>
                </article>
            </div>
        </section>
    </main>
    <footer>
        <div>
            <a href="#"><img src="./assets/images/facebook.png" alt="facebook" /></a>
            <a href="#"><img src="./assets/images/twitter.png" alt="twitter" /></a>
            <a href="#"><img src="./assets/images/linkedin.png" alt="linkedin" /></a>
            <a href="#"><img src="./assets/images/github.png" alt="github" /></a>
        </div>
        <div>
            <p>© All Right Reserved 2022</p>
        </div>
    </footer>
    <script src="./js/script.js"></script>
</body>

</html>