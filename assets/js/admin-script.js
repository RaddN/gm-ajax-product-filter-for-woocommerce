const editor = CodeMirror(document.getElementById("code-editor"), {
            value: document.getElementById("custom_template_input").value,
            mode: "text/html",
            lineNumbers: true,
            lineWrapping: true
        });

        editor.on("change", function() {
            document.getElementById("custom_template_input").value = editor.getValue();
        });


        function insertPlaceholder(placeholder) {
            const cursor = editor.getCursor();
            editor.replaceRange(placeholder, cursor);
            editor.focus();
        }