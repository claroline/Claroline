import ConfirmModalController from '../Controller/ConfirmModalController'

import errorModal from '../Partial/error_modal.html'

export default class ClarolineAPIService {
    constructor($http, $httpParamSerializerJQLike, $uibModal) {
        this.$http = $http
        this.$httpParamSerializerJQLike = $httpParamSerializerJQLike
        this.$uibModal = $uibModal
    }

    formEncode(formName, parameters) {
            var data = new FormData();

            for (var key in parameters) {
                data.append(formName + '[' + key + ']', parameters[key]);
            }

            return data;
        }

    formSerialize(formName, parameters) {
        var data = {};
        var serialized = angular.copy(parameters);
        //remove the id too
        if (serialized.id) delete serialized.id

        //quick and dirty fix for array of checkboxes. It probably won't work for (multi)select and radio buttons but... hey. It's a start.
        //I do all of this because by default, the serializer expects an array for sf2 BUT ng-init will do an object and it won't work.
        for (var key in parameters) {
            if (typeof parameters[key] === 'object') {
                var array = [];
                var object = parameters[key];

                for (var el in object) {
                    if (object[el] === true) {
                        array.push(el);
                    }
                }

                serialized[key] = array;
            }
        }

        ///q&d fixe for submission
        data[formName] = serialized;

        return this.$httpParamSerializerJQLike(data);
    }

    //replace element in array whose id is element.id
    replaceById(element, elements) {
        var index = null;
        for (let i = 0; i < elements.length; i++) {
            //lazy comparison. I don't want to cast anything
            if (element.id == elements[i].id) {
                index = i;
                break;
            }
        }

        if (index !== null) {
            elements[index] = element;
        }

        return elements;
    }

    removeElements(toRemove, elements) {
        var idxs = [];

        for (var i = 0; i < toRemove.length; i++) {
            idxs.push(elements.indexOf(toRemove[i]));
        }

        for (var i = 0; i < idxs.length; i++) {
            elements.splice(idxs[i] - i, 1);
        }

        return elements;
    }

    confirm(urlObject, callback, title, content) {
        this.$uibModal.open({
            template: require('../Partial/confirm_modal.html'),
            controller: 'ConfirmModalController',
            controllerAs: 'cmc',
            resolve: {
                callback: function() {return callback},
                urlObject: function() {return urlObject},
                title: function() {return title},
                content: function() {return content}
            }
        });
    }

    errorModal() {
        this.$uibModal.open({
            template: errorModal ,
            controller: () => {},
        });
    }

    generateQueryString(array, name) {
        var qs = '';

        for (var i = 0; i < array.length; i++) {
            var id = (array[i].id) ? array[i].id: array[i];
            qs += name + '[]=' + id + '&';
        }

        return qs;
    }
}
