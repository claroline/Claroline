export default function (commentsManager, $timeout) {
  return {
    restrict: 'A',
    link: function ($scope, element) {
      $scope.comments = commentsManager.comments

      $scope.$watch('comments.length', function (newValue, oldValue) {
        if (newValue >= oldValue) {
          $timeout(function (){
            element[0].scrollTop = element[0].scrollHeight
          }, 0)
        }
      })
    }
  }
}
