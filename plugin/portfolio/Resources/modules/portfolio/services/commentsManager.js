import angular from 'angular/index'

export default function (commentFactory){
  return {
    comments: [],
    init: function (portfolioId, comments) {
      angular.forEach(comments, function (rawComment) {
        var comment = commentFactory.getComment(portfolioId)
        this.comments.push(new comment(rawComment))
      }, this)
    },
    create: function (portfolioId, rawComment) {
      var emptyComment = commentFactory.getComment(portfolioId)
      var comment      = new emptyComment(rawComment)

      this.comments.push(comment)
      this.save(comment)
    },
    save: function (comment) {
      var success = function () {
      }
      var failed = function () {
      }

      return comment.$save(success, failed)
    }
  }
}
