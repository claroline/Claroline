export default class SearchOptionsService {
    translate(key) {
        return window.Translator.trans(key, {}, 'platform')
    }

    getOptionValue(field, search) {
        if (!field) return;
        search = !search ? '': search.trim();

        return this.translate('filter_by') + ' ' + this.translate(field).toLowerCase() + ': ' + search + '';
    }

    generateOptions(fields) {
        if (fields == undefined) return [];
        const options = [];

        for (let i = 0; i < fields.length; i++) {
            options.push(
                {
                    id: i,
                    name: this.getOptionValue(fields[i]),
                    field: fields[i],
                    value: ''
                }
            );
        }

        return options;
    }
}