Feature:
  In order to get actionable insights about posts
  As an analyst
  I want to be able to calculate total posts by week

  Scenario: User successfully calculates total posts by week
    Given there are posts loaded from file '/tests/fixtures/posts.json'
    When I generate total posts by week report from 3 post pages
    Then I should get the following stats calculated:
    """
    {
      "total_posts_by_week": {
        "2020-17": 7,
        "2020-13": 2
      }
    }
    """