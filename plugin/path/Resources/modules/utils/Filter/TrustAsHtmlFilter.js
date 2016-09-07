/**
 * Trust as HTML filter
 */

export default function trustAsHtmlFilter($sce) {
  return function (text) {
    return $sce.trustAsHtml(text)
  }
}