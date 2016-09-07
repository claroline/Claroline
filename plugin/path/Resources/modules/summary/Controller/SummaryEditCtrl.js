/**
 * Path summary edit
 */

import $ from 'jquery'

import SummaryBaseCtrl from './SummaryBaseCtrl'

export default class SummaryEditCtrl extends SummaryBaseCtrl {
  constructor(SummaryService, PathService) {
    super(SummaryService, PathService)

    /**
     * Summary sortable options
     * @type {object}
     */
    this.treeOptions = {
      dragStart: (event) => {
        // Disable tooltip on drag handlers
        $('.angular-ui-tree-handle').tooltip('disable')

        // Hide tooltip for the dragged element
        if (event.source && event.source.nodeScope && event.source.nodeScope.$element) {
          event.source.nodeScope.$element.find('.angular-ui-tree-handle').tooltip('hide')
        }
      },
      dropped: () => {
        // Enable tooltip on drag handlers
        $('.angular-ui-tree-handle').tooltip('enable')

        // Recalculate step levels
        this.pathService.reorderSteps()
      }
    }
  }
}
