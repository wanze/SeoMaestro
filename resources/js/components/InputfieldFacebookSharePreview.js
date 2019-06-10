export default class InputfieldFacebookSharePreview {
    constructor($container, fieldName) {
        this.$inputTitle = document.querySelector(`[name="${fieldName}_opengraph_title"]`);
        this.$inputTitleInherit = document.querySelector(`[name="${fieldName}_opengraph_title_inherit"]`);
        this.$inputDesc = document.querySelector(`[name="${fieldName}_opengraph_description"]`);
        this.$inputDescInherit = document.querySelector(`[name="${fieldName}_opengraph_description_inherit"]`);
        this.$inputImage = document.querySelector(`[name="${fieldName}_opengraph_image"]`);
        this.$inputImageInherit = document.querySelector(`[name="${fieldName}_opengraph_image_inherit"]`);
        this.$title = $container.querySelector('[data-title]');
        this.$desc = $container.querySelector('[data-desc]');
        this.$image = $container.querySelector('[data-image]');

        this.maxLengths = {
            title: 60,
            desc: 75
        };
    }

    init() {
        this.$title.innerHTML = this.truncateString(this.$title.innerHTML, this.maxLengths.title);
        this.$desc.innerHTML = this.truncateString(this.$desc.innerHTML, this.maxLengths.desc);

        return this.initEventListeners();
    }

    initEventListeners() {
        ['keyup', 'blur'].forEach((event) => {
            this.$inputTitle.addEventListener(event, () => {
                this.$title.innerHTML = this.renderTitleFromInput();
            });

            this.$inputDesc.addEventListener(event, () => {
                this.$desc.innerHTML = this.renderDescriptionFromInput();
            });
        });

        this.$inputImage.addEventListener('blur', () => {
            this.setBackgroundImage(this.$inputImage.value);
        });


        this.$inputTitleInherit.addEventListener('change', () => {
            const maxLength = this.maxLengths.title;

            if (this.$inputTitleInherit.checked) {
                this.$title.innerHTML = this.truncateString(this.$title.dataset.title, maxLength);
            } else {
                if (this.$inputTitle.value) {
                    this.$title.innerHTML = this.renderTitleFromInput();
                }
            }
        });

        this.$inputDescInherit.addEventListener('change', () => {
            const maxLength = this.maxLengths.desc;

            if (this.$inputDescInherit.checked) {
                this.$desc.innerHTML = this.truncateString(this.$desc.dataset.desc, maxLength);
            } else {
                if (this.$inputDesc.value) {
                    this.$desc.innerHTML = this.renderDescriptionFromInput();
                }
            }
        });

        this.$inputImageInherit.addEventListener('change', () => {
            if (this.$inputImageInherit.checked) {
                this.setBackgroundImage(this.$image.dataset.image);
            } else {
                if (this.$inputImage.value) {
                    this.setBackgroundImage(this.$inputImage.value);
                }
            }
        });

        return this;
    }

    renderTitleFromInput() {
        return this.truncateString(this.$inputTitle.value, this.maxLengths.title);
    }

    renderDescriptionFromInput() {
        return this.truncateString(this.$inputDesc.value, this.maxLengths.desc);
    }

    setBackgroundImage(imageUrl) {
        // this.$image.removeAttribute('style');
        // console.log(this.$image.style.backgroundImage);
        // this.$image.style.backgroundImage = `url('${imageUrl}');`;
        this.$image.setAttribute('style', `background-image: url('${imageUrl}');`);
        // console.log(imageUrl);
        // console.log(this.$image.style.backgroundImage);
    }

    truncateString(string, maxLength) {
        if (string.length < maxLength) {
            return string;
        }

        const str = string.substring(0, maxLength);

        return str ? `${str}...` : '';
    }
}
