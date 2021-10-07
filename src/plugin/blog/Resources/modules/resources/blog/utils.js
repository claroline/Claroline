import isEmpty from 'lodash/isEmpty'
import find from 'lodash/find'

import {actions as listActions} from '#/main/app/content/list/store'

import {constants} from '#/plugin/blog/resources/blog/constants'
import {selectors} from '#/plugin/blog/resources/blog/store/selectors'
import {actions as postActions} from '#/plugin/blog/resources/blog/post/store/actions'

function cleanTag(mode, tag){
  if(mode === constants.TAGCLOUD_TYPE_CLASSIC_NUM) {
    tag = tag.replace(/ *\([0-9+]*\) */g, '')
  }

  return tag
}

function parseQuery(queryString) {
  var query = {}
  var pairs = (queryString[0] === '?' ? queryString.substr(1) : queryString).split('&')
  for (var i = 0; i < pairs.length; i++) {
    var pair = pairs[i].split('=')
    query[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || '')
  }
  return query
}

function initDatalistFilters(dispatch, query){
  let obj = parseQuery(query)
  if(!isEmpty(obj))
  {
    if(!isEmpty(obj['tag'])){
      dispatch(listActions.addFilter(selectors.STORE_NAME+'.posts', 'tag', obj['tag']))
      dispatch(postActions.initDataList())
    }
    if(!isEmpty(obj['author'])){
      dispatch(listActions.addFilter(selectors.STORE_NAME+'.posts', 'author', obj['author']))
      dispatch(postActions.initDataList())
    }
  }
}

function buildQueryParameters(filters){
  let query = ''
  let authorParameter

  let tags = find(filters, ['property', 'tags'])
  if(!isEmpty(tags)){
    query = '?tags=' + tags['value']
  }
  let authorName = find(filters, ['property', 'author'])
  if(!isEmpty(authorName)){
    authorParameter = 'author=' + authorName['value']
    query === '' ? query = '?' + authorParameter : '&' + authorParameter
  }
  return encodeURI(query)
}

function updateQueryParameters(uri, key, value) {
  var re = new RegExp('([?&])' + key + '=.*?(&|$)', 'i')
  var separator = uri.indexOf('?') !== -1 ? '&' : '?'
  if (uri.match(re)) {
    return uri.replace(re, '$1' + key + '=' + value + '$2')
  }
  else {
    return uri + separator + key + '=' + value
  }
}

export {
  cleanTag,
  parseQuery,
  initDatalistFilters,
  buildQueryParameters,
  updateQueryParameters
}