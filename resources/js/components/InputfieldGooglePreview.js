export default class InputfieldGooglePreview {

    constructor($container, fieldName) {
        this.$inputTitle = document.querySelector(`[name="${fieldName}_meta_title"]`);
        this.$inputTitleInherit = document.querySelector(`[name="${fieldName}_meta_title_inherit"]`);
        this.$title = $container.querySelector('[data-title]');
        this.$inputDesc = document.querySelector(`[name="${fieldName}_meta_description"]`);
        this.$desc = $container.querySelector('[data-desc]');
        this.$inputDescInherit = document.querySelector(`[name="${fieldName}_meta_description_inherit"]`);
        this.titleFormat = $container.dataset.seomaestroTitleFormat;

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
                this.$title.innerHTML = this.renderTitleFromInput();
            });

            this.$inputDesc.addEventListener(event, () => {
                this.$desc.innerHTML = this.renderDescriptionFromInput();
            });
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

        return this;
    }

    renderTitleFromInput() {
        if (!this.titleFormat) {
            return this.truncateString(this.$inputTitle.value, this.maxLengths.title);
        }

        const title = this.titleFormat.replace('{meta_title}', this.$inputTitle.value);

        return this.truncateString(title, this.maxLengths.title);
    }

    renderDescriptionFromInput() {
        return this.truncateString(this.$inputDesc.value, this.maxLengths.desc);
    }

    truncateString(string, maxLength) {
        if (string.length < maxLength) {
            return string;
        }

        const str = string.substring(0, maxLength);

        return str ? `${str}...` : '';
    }
}
