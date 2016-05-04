export default class ChapterFactory {
  
  constructor() {
    this.current = {}
    this.edited = {}
  }

  refresh(chapter) {
    this.current.id = chapter.id
    this.current.title = this.edited.title = chapter.title
    this.current.text = this.edited.text = chapter.text
    this.current.slug = chapter.slug
    this.current.parent = chapter.parent
    this.current.previous = chapter.previous
    this.current.next = chapter.next
    this.current.hasChildren = chapter.hasChildren
  }
}