export default class InputfieldGooglePreview {

    constructor($container, fieldName) {
        this.$inputTitle = document.querySelector(`[name="${fieldName}_meta_title"]`);
        this.$inputTitleInherit = document.querySelector(`[name="${fieldName}_meta_title_inherit"]`);
        this.$title = $container.querySelector('[data-title]');
        this.$inputDesc = document.querySelector(`[name="${fieldName}_meta_description"]`);
        this.$desc = $container.querySelector('[data-desc]');
        this.$inputDescInherit = document.querySelector(`[name="${fieldName}_meta_description_inherit"]`);

        this.maxLengths = {
            title: 60,
            desc: 160
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
                this.$title.innerHTML = this.truncateString(this.$inputTitle.value, this.maxLengths.title);
            });

            this.$inputDesc.addEventListener(event, () => {
                this.$desc.innerHTML = this.truncateString(this.$inputDesc.value, this.maxLengths.desc);
            });
        });

        this.$inputTitleInherit.addEventListener('change', () => {
            const maxLength = this.maxLengths.title;

            if (this.$inputTitleInherit.checked) {
                this.$title.innerHTML = this.truncateString(this.$title.dataset.title, maxLength);
            } else {
                if (this.$inputTitle.value) {
                    this.$title.innerHTML = this.truncateString(this.$inputTitle.value, maxLength);
                }
            }
        });

        this.$inputDescInherit.addEventListener('change', () => {
            const maxLength = this.maxLengths.desc;

            if (this.$inputDescInherit.checked) {
                this.$desc.innerHTML = this.truncateString(this.$desc.dataset.desc, maxLength);
            } else {
                if (this.$inputDesc.value) {
                    this.$desc.innerHTML = this.truncateString(this.$inputDesc.value, maxLength);
                }
            }
        });

        return this;
    }

    truncateString(string, maxLength) {
        if (string.length < maxLength) {
            return string;
        }

        const str = string.substring(0, maxLength);

        return str ? `${str}...` : '';
    }
}
