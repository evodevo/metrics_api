Feature:
  In order to get actionable insights about posts
  As an analyst
  I want to be able to calculate average posts per user by month

  Scenario: User successfully generates average posts per user
    Given there are posts loaded from file '/tests/fixtures/posts.json'
    When I generate average posts per user report from 3 post pages
    Then I should get the following stats calculated:
    """
    {
      "avg_posts_per_user": {
        "2020-Apr": 1.17,
        "2020-Mar": 1
      }
    }
    """