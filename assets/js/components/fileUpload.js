window.jsImageUpload = function (inputElement, csrfToken, uploadUrl, translations, dropElement) {

    let uploadFunction = function(file) {
        let previewDiv = document.createElement('div');
        previewDiv.classList.add('col');
        let previewImg = document.createElement('img');

        let fileReader = new FileReader();
        fileReader.onload = function() {
            let output = previewImg;
            output.src = fileReader.result;
        };
        fileReader.readAsDataURL(file);

        previewDiv.appendChild(previewImg);
        document.getElementById('file_upload_progress').appendChild(previewDiv);

        let progressText = document.createElement('div');
        progressText.classList.add('upload_progress_text');
        progressText.innerText = 'pending';
        previewDiv.appendChild(progressText);

        if (file.size > 32 * 1024 * 1024) {
            progressText.innerText = 'File is too big';

            return;
        }

        let formData = new FormData();
        formData.append('file', file);
        formData.append('token', csrfToken)
        let xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function(e) {
            const percent_complete = ((e.loaded / e.total)*100).toFixed(1);
            progressText.innerText = percent_complete.toString() + '%';
        });

        xhr.addEventListener('load', function(e) {
            let responseObject = JSON.parse(xhr.response);
            if (responseObject.status === 0) {
                progressText.innerText = translations.uploaded;
            } else {
                progressText.innerText = responseObject.error;
            }
        });

        xhr.open('POST', uploadUrl, true);
        xhr.send(formData);
    };

    inputElement.addEventListener('change', function() {
        for (let i = 0; i < this.files.length; i++) {
            uploadFunction(this.files[i]);
        }
    });

    if (dropElement) {
        dropElement.addEventListener(
            'dragover',
            function(ev) {
                ev.preventDefault();
            },
            false
        );

        dropElement.addEventListener(
            'drop',
            function (ev) {
                ev.preventDefault();
                let files = ev.dataTransfer.files;
                for (let i = 0; i < files.length; i++ ) {
                    uploadFunction(files[i]);
                }
            },
            false
        );
    }
};
