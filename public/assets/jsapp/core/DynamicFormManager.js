export class DynamicFormManager {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.formDataObject = {
            videos: [],
            social: []
        };
    }

    // Метод для добавления нового элемента
    addElement(blockType, platform,classname) {
        const newElement = document.createElement('div');
        newElement.classList.add('mb-3', 'dynamic-container-block');

         if (blockType === 'addbutton') {
            let textconte = '';
            let hiden = '';
            let randomName = '';
            let box_content = document.getElementById('savebtn').value;
            if (box_content =='button') {
                textconte = 'button';
                randomName = `btn_${Math.random().toString(36).substr(2, 9)}`;
                hiden = 'd-none';
            } else {
                textconte = 'link';
                randomName = '';
                hiden = '';
            }
            newElement.innerHTML = `
                <div class="input-group mb-3">
                <div class="row" style="width: 100%;"> 
                <div class="col-lg-4 mr-2"><input type="text" class="form-control ${classname}"  name="nameinput[]" placeholder="namebutton"></div>
                <div class="col-lg-4 mr-2 ${hiden}"><input type="text" class="form-control ${classname}"  value="${randomName}"  name="valueinput[]">
                <input type="text" class="form-control ${classname}" hidden="hidden" value="${textconte}" name="elementselect[]">
                </div>
                <div class="col-lg-4 mr-2"><button type="button" class="btn btn-danger remove-element">Remove</button></div>
                </div>
                    
                    
                    
                </div>
            `;
        }

        // Добавляем элемент в контейнер
        this.container.appendChild(newElement);

        // Добавляем обработчик для удаления элемента
        newElement.querySelector('.remove-element').addEventListener('click', (e) => {
            e.target.closest('.dynamic-container-block').remove();
        });
    }

    // Метод для сбора данных
    collectFormData() {
        // Очищаем предыдущие данные
        this.formDataObject.videos = [];
        this.formDataObject.social = [];

        // Сбор данных с динамических полей
        this.container.querySelectorAll('input').forEach((input) => {
            const name = input.getAttribute('name');
            if (name.startsWith('video_')) {
                this.formDataObject.videos.push({ platform: name.split('_')[1], url: input.value });
            } else if (name.startsWith('social_')) {
                this.formDataObject.social.push({ platform: name.split('_')[1], url: input.value });
            }
        });

        return this.formDataObject;
    }

    // Метод для отправки данных на сервер
    sendFormData() {
        const collectedData = this.collectFormData();

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/addproduct', true);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                console.log(xhr.responseText);
            } else if (xhr.readyState == 4) {
                console.error('Error: ' + xhr.status);
            }
        };

        xhr.send(JSON.stringify(collectedData));
    }
}