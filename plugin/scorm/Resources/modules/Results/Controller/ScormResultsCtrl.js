/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class ScormResultsCtrl {

  constructor(NgTableParams, ScormResultsService) {
    this.NgTableParams = NgTableParams
    this.ScormResultsService = ScormResultsService
    this.type = ScormResultsService.getType()
    this.scos = ScormResultsService.getScos()
    this.scosTrackings = ScormResultsService.getScosTrackings()
    this.trackingsDetails = ScormResultsService.getTrackingsDetails()
    this.isCollapsed = {}
    this.scosTableParams = {}
    this.initializeTableParams()
  }

  initializeTableParams () {
    this.scos.forEach(s => {
      const scoId = s['id']
      this.scosTableParams[scoId] = new this.NgTableParams(
        {count: 20},
        {counts: [10, 20, 50, 100], dataset: this.scosTrackings[scoId]}
      )
    })
  }

  loadDetails (userId, trackingId, scoId) {
    this.ScormResultsService.loadTrackingDetails(userId, trackingId, scoId)
  }
}