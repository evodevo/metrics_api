Feature:
  In order to get actionable insights about posts
  As an analyst
  I want to be able to calculate average post char lengths by month

  Scenario: User successfully generates average post char lengths
    Given there are posts loaded from file '/tests/fixtures/posts.json'
    When I generate average post char lengths report from 3 post pages
    Then I should get the following stats calculated:
    """
    {
      "avg_post_char_lengths": {
          "2020-Apr": 365.29,
          "2020-Mar": 383
      }
    }
    """