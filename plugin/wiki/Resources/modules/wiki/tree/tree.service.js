let _url = new WeakMap()
let _$resource = new WeakMap()
let _wiki = new WeakMap()

export default class TreeService {
  constructor(url, $resource, wiki) {
    _url.set(this, url)
    _$resource.set(this, $resource)
    _wiki.set(this, wiki)
  }

  moveSection(wiki, sect, newParent, newPreviousSibling) {
    const url = _url.get(this)('icap_wiki_api_move_wiki_section', {
      'wiki': wiki.id,
      'section': sect.id
    })
    let Section = _$resource.get(this)(url, null, {
      'move': { method: 'POST' }
    })
    let section = new Section()
    section.newParent = newParent !== null ? newParent.id : null
    section.newPreviousSibling = newPreviousSibling !== null ? newPreviousSibling.id : null

    return section.$move(
      () => {},
      failure => {
        _wiki.get(this).sections = failure.sections
      }
    )
  }

}

TreeService.$inject = [
  'url',
  '$resource',
  'wiki'
]
