import ConfirmModalController from './../Controller/ConfirmModalController'
import confirmTpl from './../Partial/confirm-modal.html'

export default class ConfirmModalDirective {
    constructor () {
        this.restrict = 'A'
        this.replace = false
        this.scope = {
            /**
             * Content message
             */
            content: '@confirmModal',

            /**
             * Callback to execute if the User confirm his action
             */
            confirmCallback: '&confirmModalAction',

            /**
             * Callback to execute if the User cancel his action
             */
            cancelCallback: '&confirmModalCancel'
        }
        this.bindToController = true
        this.controllerAs = 'modalCtrl'
        this.controller = [
            '$uibModal',
            class ModalCtrl {
                constructor ($uibModal) {
                    this.$uibModal = $uibModal
                }

                open() {
                    var message = this.content;
                    this.$uibModal.open({
                        template: confirmTpl,
                        controller: ConfirmModalController,
                        controllerAs: 'confirmCtrl',
                        resolve: {
                            content: () => message
                        }
                    }).result.then(
                        // On confirm
                        d => {
                            if (typeof this.confirmCallback === 'function') {
                                this.confirmCallback();
                            }
                        },

                        // On cancel
                        d => {
                            if (typeof this.cancelCallback === 'function') {
                                this.cancelCallback();
                            }
                        }
                    )
                }
            }
        ]
        this.link = function link(scope, element, attr, controller) {
            // Open the modal on click on the element
            element.on('click', controller.open.bind(controller));

            scope.$on('$destroy', () => {
                element.off('click', controller.open.bind(controller));
            })
        }
    }
}
