/**
 * Created by david on 08/02/17.
 */

import jQuery from 'jquery'
import '#/main/core/jquery/tag-canvas/jquery.tagcanvas.min.js'

(function ($) {
  $(function () {
    $('#tagCloudCanvas').tagcanvas({
      textColour : '#428BCA',
      outlineThickness : 1,
      maxSpeed : 0.05,
      textHeight: 18,
      depth : 0.5,
      weight : true,
      outlineColour : '#2A6496',
      outlineMethod : 'colour',
      reverse : true
    }, 'tagList')
  })
})(jQuery)
