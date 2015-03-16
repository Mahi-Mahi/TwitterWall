wget -O wearepatients.sql.gz http://twitterwall:jaeVe5ai@bnp-twitter-wall.web-staging.com/wp-content/plugins/bdd-export/bdd-export.php?bdd_export_key=54784e4664dc2
# wearepatients:Xueng8we@
gunzip -f wearepatients.sql.gz
mysql -u root wearepatients < wearepatients.sql
say "done"


#rm wearepatients.sql
