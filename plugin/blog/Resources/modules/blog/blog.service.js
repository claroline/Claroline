let _blogData = new WeakMap()
let _url = new WeakMap()
let _$resource = new WeakMap()
let _Upload = new WeakMap()
let _uibDateParser = new WeakMap()
let _$q = new WeakMap()

export default class BlogService {
  
  constructor(blogData, url, $resource, Upload, uibDateParser, $q) {
    
    _blogData.set(this, blogData)
    _url.set(this, url)
    _$resource.set(this, $resource)
    _Upload.set(this, Upload)
    _uibDateParser.set(this, uibDateParser)
    _$q.set(this, $q)

    this.posts = []
    this.info = ''

    this.totalItems = null
    this.fixedTitle = null
    this.currentPost = null
    this.newPost = null
    this.tempInfo = this.info
  }

  get id() { return _blogData.get(this).id }
  get name() { return _blogData.get(this).name }
  get bannerImgStyle() { return _blogData.get(this).bannerImgStyle }
  get panels() { return _blogData.get(this).panels }
  set panels(panels) { return _blogData.get(this).panels = panels }
  get archives() { return _blogData.get(this).archives }
  set archives(archives) { _blogData.get(this).archives = archives }
  get isGrantedAdmin() { return _blogData.get(this).isGrantedAdmin }
  get isGrantedEdit() { return _blogData.get(this).isGrantedEdit }
  get isGrantedPost() { return _blogData.get(this).isGrantedPost }
  get authors() { return _blogData.get(this).authors }
  set authors(authors) { _blogData.get(this).authors = authors }
  get rssUrl() { return _blogData.get(this).rssUrl }
  get options() { return _blogData.get(this).options }
  set options(options) { _blogData.get(this).options = options }
  get tags() { return _blogData.get(this).tags }
  set tags(tags) { return _blogData.get(this).tags = tags }
  get eventSources() { return _blogData.get(this).eventSources }
  get img_dir() { return _blogData.get(this).img_dir  }
  get banner_dir() { return _blogData.get(this).banner_dir  }
  get user() { return _blogData.get(this).user }
  get loginUrl() { return _blogData.get(this).loginUrl }

  getInfo() {
    const url = _url.get(this)('icap_blog_api_get_blog', {
      'blog': this.id
    })

    let Info = _$resource.get(this)(url)
    Info.get(
      success => {
        this.info = success.info
      }
    )
  }

  getPosts(page = null) {
    const url = _url.get(this)('icap_blog_api_get_blog_post', {
      'blog': this.id,
      'page': page
    })

    let Posts = _$resource.get(this)(url)
    let posts = Posts.get(
      success => {
        this.posts = success.posts
        this.totalItems = success.total
      }
    )
    
    return posts.$promise
  }

  getPostsByTag(tag, page = null) {
    const url = _url.get(this)('icap_blog_api_get_blog_tags_posts', {
      'blog': this.id,
      'tagId': tag,
      'page': page
    })

    let Posts = _$resource.get(this)(url)
    let posts = Posts.get(
      success => {
        this.posts = success.posts
        this.totalItems = success.total
      }
    )

    return posts.$promise
  }

  getPostsByAuthor(author, page = null) {
    const url = _url.get(this)('icap_blog_api_get_blog_authors_posts', {
      'blog': this.id,
      'author': author,
      'page': page
    })

    let Posts = _$resource.get(this)(url)
    let posts = Posts.get(
      success => {
        this.posts = success.posts
        this.totalItems = success.total
      }
    )

    return posts.$promise
  }

  getPostsBySearch(terms, page = null) {
    const url = _url.get(this)('icap_blog_api_get_blog_search', {
      'blog': this.id,
      'search': terms,
      'page': page
    })

    let Posts = _$resource.get(this)(url)
    let posts = Posts.get(
      success => {
        this.posts = success.posts
        this.totalItems = success.total
      }
    )
    
    return posts.$promise
  }

  getPostsByDay(year, month, day, page = null) {
    const url = _url.get(this)('icap_blog_api_get_blog_days_posts', {
      'blog': this.id,
      'day': `${day}-${month}-${year}`,
      'page': page
    })

    let Posts = _$resource.get(this)(url)
    let posts = Posts.get(
      success => {
        this.posts = success.posts
        this.totalItems = success.total
      }
    )

    return posts.$promise
  }

  getPostsByMonth(year, month, page = null) {
    const url = _url.get(this)('icap_blog_api_get_blog_months_posts', {
      'blog': this.id,
      'month': `${month}-${year}`,
      'page': page
    })

    let Posts = _$resource.get(this)(url)
    let posts = Posts.get(
      success => {
        this.posts = success.posts
        this.totalItems = success.total
      }
    )

    return posts.$promise
  }

  uploadBanner(file) {
    if (file === null) {
      return _$q.get(this).resolve()
    }

    const url = _url.get(this)('icap_blog_api_post_blog_banner', {
      'blog': this.id
    })
        
    return _Upload.get(this).upload({
      url: url,
      data: { file: file}
    })
    
  }

  removeBanner() {
    const url = _url.get(this)('icap_blog_api_delete_blog_banners', {
      'blog': this.id
    })
    let Banner = _$resource.get(this)(url, null, {
      'delete': { method: 'DELETE'}
    })
    let banner = new Banner()
    return banner.$delete(
      () => {
        this.banner_background_image = null
      }
    )
  }

  togglePostVisibility(post) {
    const url = _url.get(this)('icap_blog_api_put_blog_post_visibility', {
      'blog': this.id,
      'post': post.id
    })

    let Post = _$resource.get(this)(url, null, {
      'toggleVisibility': { method: 'PUT' }
    })

    let newPost = new Post(post)
    newPost.is_published = !post.is_published

    return newPost.$toggleVisibility(
      success => {
        post.is_published = success.is_published
      },
      failure => {
        post.is_published = failure.is_published
      }
    )
  }

  toggleCommentVisibility(comment, post) {
    const url = _url.get(this)('icap_blog_api_put_blog_post_comment_visibility', {
      'blog': this.id,
      'post': post.id,
      'comment': comment.id
    })

    let Comment = _$resource.get(this)(url, null, {
      'toggleVisibility': { method: 'PUT' }
    })

    let newComment = new Comment(post)
    newComment.is_published = !comment.is_published

    return newComment.$toggleVisibility(
      success => {
        comment.is_published = success.is_published
      },
      failure => {
        comment.is_published = failure.is_published
      }
    )
  }
  
  _updateGeneralInfo() {
    // Authors
    this._fetchAuthors()
    
    // Tags
    this._fetchTags()

    // Archives
    this._fetchArchives()
  }

  _fetchAuthors() {
    const url = _url.get(this)('icap_blog_api_get_blog_authors', {
      'blog': this.id
    })

    let Authors = _$resource.get(this)(url)
    Authors.query(
      success => {
        this.authors = success
      }
    )
  }

  _fetchTags() {
    const url = _url.get(this)('icap_blog_api_get_blog_tags', {
      'blog': this.id
    })

    let Tags = _$resource.get(this)(url)
    Tags.query(
      success => {
        this.tags = success
      }
    )
  }

  _fetchArchives() {
    const url = _url.get(this)('icap_blog_api_get_blog_archives', {
      'blog': this.id
    })

    let Archives = _$resource.get(this)(url)
    Archives.get(
      success => {
        this.archives = success
      }
    )
  }
  
  createPost() {
    const url = _url.get(this)('icap_blog_api_post_blog_post', {
      'blog': this.id
    })

    let Post = _$resource.get(this)(url)
    let post = new Post(this.newPost)

    return post.$save(
      () => {
        this._updateGeneralInfo()        
      }
    )

  }
  
  deletePost(post, page) {

    const url = _url.get(this)('icap_blog_api_delete_blog_post', {
      'blog': this.id,
      'post': post.id
    })

    let Post = _$resource.get(this)(url)
    let postToDelete = new Post(post)
    return postToDelete.$delete(
      () => {
        // The post to delete is the last of the page, fetch the previous one
        if (this.posts.length === 1 && page > 2) {
          page--
        }

        this.getPosts(page)

        this._updateGeneralInfo()
      }
    )
  }

  deleteComment(comment, post) {
    const url = _url.get(this)('icap_blog_api_delete_blog_post_comment', {
      'blog': this.id,
      'post': post.id,
      'comment': comment.id
    })

    let Comment = _$resource.get(this)(url)
    let commentToDelete = new Comment(comment)
    return commentToDelete.$delete(
      success => {
        post.comments = success.comments
      }
    )
  }

  setCurrentPost(post) {
    this.currentPost = post
    this.currentPost.publication_date = post.publication_date ? new Date(post.publication_date) : null
    this.fixedTitle = post.title
  }

  setCurrentPostBySlug(slug) {
    const url = _url.get(this)('icap_blog_api_get_blog_post', {
      'blog': this.id,
      'postId': slug
    })

    let Post = _$resource.get(this)(url, null, {
      'get': { method: 'GET'}
    })
    let post = new Post()

    return post.$get(
      success => {
        this.setCurrentPost(success)
      }
    )
  }

  editOptions() {
    const url = _url.get(this)('icap_blog_api_put_blog_options', {
      'blog': this.id
    })

    let Options = _$resource.get(this)(url, null, {
      'edit': { method: 'PUT' }
    })

    let options = new Options(this.optionsCopy)

    return options.$edit(
      success => {
        this.panels = this.panelsCopy
        this.options = success
      }
    )
  }
  
  editInfo(info) {
    const url = _url.get(this)('icap_blog_api_put_blog', {
      'blog': this.id
    })

    let Blog = _$resource.get(this)(url, null, {
      'edit': { method: 'PUT' }
    })

    let blog = new Blog({info: info})

    return blog.$edit(
      success => {
        this.info = success.info
      }
    )
  }

  editPost() {
    const url = _url.get(this)('icap_blog_api_put_blog_post', {
      'blog': this.id,
      'post': this.currentPost.id
    })

    let Post = _$resource.get(this)(url, null, {
      'edit': { method: 'PUT' }
    })

    let post = new Post({
      'title': this.currentPost.title,
      'content': this.currentPost.content,
      'publication_date': this.currentPost.publication_date,
      'tags': this.currentPost.tags
    })

    return post.$edit(
      success => {
        this.setCurrentPost(success)

        this._updateGeneralInfo()
      }
    )
  }

  addComment(post, message) {
    const url = _url.get(this)('icap_blog_api_put_blog_post_comment', {
      'blog': this.id,
      'post': post.id
    })

    let Comment = _$resource.get(this)(url)

    let comment = new Comment({
      'message': message
    })

    return comment.$save(
      success => {
        post.comments.push(success)
      }
    )
  }

  editComment(post, comment) {
    const url = _url.get(this)('icap_blog_api_put_blog_post_comment', {
      'blog': this.id,
      'post': post.id,
      'comment': comment.id
    })
    
    let Comment = _$resource.get(this)(url, null, {
      'edit': { method: 'PUT' }
    })

    let updatedComment = new Comment(comment.tempData)

    return updatedComment.$edit(
      success => {
        comment.message = success.message
        comment.update_date = success.update_date
      }
    )

  }

}

BlogService.$inject = [
  'blog.data',
  'url',
  '$resource',
  'Upload',
  'uibDateParser',
  '$q'
]