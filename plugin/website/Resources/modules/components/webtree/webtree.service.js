/**
 * Created by panos on 3/31/16.
 */
let _$http = new WeakMap()
let _$q = new WeakMap()
export default class WebtreeService {
  constructor ($http, $q, websiteData) {
    _$http.set(this, $http)
    _$q.set(this, $q)
    this.websiteId = websiteData.id
    this.baseUrl = this._path('icap_website_view')
  }

  appendNewNode (node) {
    let newNode = this.emptyNode
    newNode.parent = node
    node.children.push(newNode)

    return newNode
  }

  deleteNode (node) {
    let parent = node.parent
    let index = parent.children.indexOf(node)
    if (index != -1) {
      parent.children.splice(index, 1)
    }
    if (node.id !== 0) {
      let path = this._path('icap_website_page_delete', {'pageId': node.id})
      let q = this._request('delete', path).then(
        () => {
          return this._resolve(node)
        }, data => {
          parent.children.splice(index, 0, node)
          return this._reject(data)
        }
      )

      return q
    }
  }

  moveNode (node, oldParent, newParent, oldIndex, newIndex) {
    let previousSiblingId = 0
    if (newIndex > 0) {
      previousSiblingId = newParent.children[ newIndex - 1 ].id
    }
    node.parent = newParent
    let path = this._path('icap_website_page_move', {
      'pageId': node.id,
      'newParentId': newParent.id,
      'previousSiblingId': previousSiblingId
    })
    let q = this._request('put', path).then(
      data => {
        return this._resolve(data)
      }, data => {
        this._cancelMove(node, oldParent, newParent, oldIndex, newIndex)
        return this._reject(data)
      }
    )

    return q
  }

  saveNode (node) {
    if (node.new) {
      return this.createNode(node)
    } else {
      return this.updateNode(node)
    }
  }

  createNode (node) {
    node.new = false
    let path = this._path('icap_website_page_post', { 'parentPageId': node.parent.id })
    let q = this._request('post', path, this._jsonNode(node)).then(
        data => {
          node.id = data.id
          return this._resolve(node)
        }, () => {
          return this._reject(node)
        }
    )

    return q
  }

  updateNode (node) {
    node.saving = true
    let path = this._path('icap_website_page_put', { 'pageId': node.id })
    let q = this._request('put', path, this._jsonNode(node)).then(
      () => {
        node.saving = false
        return this._resolve(node)
      }, () => {
        node.saving = false
        return this._reject(node)
      }
    )

    return q
  }

  setHomepage (newHomepage, oldHomepage) {
    let path = this._path('icap_website_page_set_homepage', { 'pageId': newHomepage.id })
    let q = this._request('put', path).then(
      () => {
        if (oldHomepage != null) oldHomepage.isHomepage = false
        newHomepage.isHomepage = true
        return this._resolve(newHomepage)
      }, data => {
        newHomepage.isHomepage = false
        if (oldHomepage != null) oldHomepage.isHomepage = true
        return this._reject(data)
      }
    )

    return q
  }

  copyNodeInfo (node) {
    return this._jsonNode(node)
  }

  get emptyNode () {
    return {
      id: 0,
      title: '',
      description: '',
      visible: true,
      isSection: false,
      type: "blank",
      richText: null,
      url: null,
      resourceNode: null,
      resourceNodeType: null,
      isHomepage: false,
      target: 0,
      children: [],
      new: true
    }
  }

  _jsonNode (node) {
    return this._pick(node, [
      'title',
      'description',
      'visible',
      'isSection',
      'type',
      'richText',
      'url',
      'target',
      'resourceNode',
      'resourceNodeType'
    ])
  }

  _pick (node, props) {
    let obj = {}
    for (let prop of props) {
      obj[prop] = node[prop]
    }

    return obj
  }

  _path (name, params = {}) {
    if (!params['websiteId']) {
      params['websiteId'] = this.websiteId
    }
    return window.Routing.generate(name, params)
  }

  _cancelMove (node, oldParent, newParent, oldIndex, newIndex) {
    newParent.children.splice(newIndex, 1)
    oldParent.children.splice(oldIndex, 0, node)
    node.parent = oldParent
  }

  _request (method, path, data = null, config = {}) {
    return _$http.get(this)({method: method, url: path, data: data, config: config}).then(
      response => {
        if (typeof response.data === 'object') {
          return this._resolve(response.data)
        } else {
          return this._reject(response.data)
        }
      }, response => {
        return this._reject(response.data)
      }
    )
  }

  _reject (data) {
    return _$q.get(this).reject(data)
  }

  _resolve (data) {
    return _$q.get(this).resolve(data)
  }
}

WebtreeService.$inject = [ '$http', '$q', 'website.data']
