<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juntar PDFs</title>
    <style>
        #fileList {
            list-style-type: none;
            padding: 0;
        }
        #fileList li {
            margin: 5px 0;
            padding: 10px;
            background: #f0f0f0;
            border: 1px solid #ddd;
            cursor: move;
            user-select: none; /* Desativa a seleção de texto */
        }
        .tabela {
            margin-top: 15px;
            width: 600px;
            padding: 15px;
            margin-left: auto;
            margin-right: auto;

            border-radius: 5px;
            border-width: 1px;
            border-style: solid;
            border-color: black;
            text-align: center;
        }
    </style>
    <script>
        function updateFileList() {
            const fileInput = document.getElementById('pdfs');
            const fileList = document.getElementById('fileList');
            const orderInput = document.getElementById('order');
            fileList.innerHTML = '';

            Array.from(fileInput.files).forEach((file, index) => {
                const li = document.createElement('li');
                li.innerHTML = `${file.name} <span style="float: right;">Ordem: ${index + 1}</span>`;
                li.setAttribute('data-index', index + 1);
                li.setAttribute('draggable', true); // Permite que o item seja arrastável
                fileList.appendChild(li);
            });

            updateOrder();
        }

        function updateOrder() {
            const fileList = document.getElementById('fileList');
            const orderInput = document.getElementById('order');
            const order = Array.from(fileList.children).map(li => li.getAttribute('data-index'));
            orderInput.value = order.join(',');
        }

        function allowDrop(event) {
            event.preventDefault();
        }

        function drag(event) {
            event.dataTransfer.setData('text', event.target.getAttribute('data-index'));
        }

        function drop(event) {
            event.preventDefault();
            const draggedIndex = event.dataTransfer.getData('text');
            const target = event.target.closest('li');
            if (target && target.getAttribute('data-index') !== draggedIndex) {
                const fileList = document.getElementById('fileList');
                const draggedItem = fileList.querySelector(`li[data-index="${draggedIndex}"]`);
                fileList.insertBefore(draggedItem, target.nextSibling);
                updateOrder();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const fileList = document.getElementById('fileList');
            fileList.addEventListener('dragover', allowDrop);
            fileList.addEventListener('dragstart', drag);
            fileList.addEventListener('drop', drop);
        });
    </script>
</head>
<body>
    <div class="tabela">
        <h1>Juntar PDFs</h1>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <label for="pdfs">Selecione os PDFs para juntar:</label>
            <input type="file" id="pdfs" name="pdfs[]" multiple required onchange="updateFileList()">
            <br><br>
            <ul id="fileList"></ul>
            <br><br>
            <label for="order">Informe a ordem para juntar os PDFs (separados por vírgula):</label>
            <input type="text" id="order" name="order" required readonly>
            <br><br>
            <input type="submit" value="Juntar PDFs">
        </form>
    </div>
</body>
</html>