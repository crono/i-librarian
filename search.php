<?php
$microtime1 = microtime(true);
include_once 'data.php';
include_once 'functions.php';

if (isset($_GET['newsearch'])) {
    unset($_SESSION['session_anywhere']);
    unset($_SESSION['session_anywhere_separator']);
    unset($_SESSION['session_authors']);
    unset($_SESSION['session_authors_separator']);
    unset($_SESSION['session_journal']);
    unset($_SESSION['session_journal_separator']);
    unset($_SESSION['session_title']);
    unset($_SESSION['session_title_separator']);
    unset($_SESSION['session_abstract']);
    unset($_SESSION['session_abstract_separator']);
    unset($_SESSION['session_keywords']);
    unset($_SESSION['session_keywords_separator']);
    unset($_SESSION['session_secondary_title']);
    unset($_SESSION['session_secondary_title_separator']);
    unset($_SESSION['session_tertiary_title']);
    unset($_SESSION['session_tertiary_title_separator']);
    unset($_SESSION['session_affiliation']);
    unset($_SESSION['session_affiliation_separator']);
    unset($_SESSION['session_search_id']);
    unset($_SESSION['session_year']);
    unset($_SESSION['session_fulltext']);
    unset($_SESSION['session_fulltext_separator']);
    unset($_SESSION['session_custom1']);
    unset($_SESSION['session_custom1_separator']);
    unset($_SESSION['session_custom2']);
    unset($_SESSION['session_custom2_separator']);
    unset($_SESSION['session_custom3']);
    unset($_SESSION['session_custom3_separator']);
    unset($_SESSION['session_custom4']);
    unset($_SESSION['session_custom4_separator']);
    unset($_SESSION['session_notes']);
    unset($_SESSION['session_notes_separator']);
    unset($_SESSION['session_pdfnotes']);
    unset($_SESSION['session_pdfnotes_separator']);
    unset($_SESSION['session_category']);
    unset($_SESSION['session_whole_words']);
    unset($_SESSION['session_case']);
    unset($_SESSION['session_search-metadata']);
    unset($_SESSION['session_search-pdfs']);
    unset($_SESSION['session_search-pdfnotes']);
    unset($_SESSION['session_search-notes']);
    unset($_SESSION['session_include-categories']);
    unset($_SESSION['session_reference_type']);
    unset($_SESSION['rating[]']);
    die();
}

if (isset($_GET['savesearch']) && !empty($_GET['searchname'])) {

    database_connect(IL_DATABASE_PATH, 'library');

    $stmt = $dbHandle->prepare("DELETE FROM searches WHERE userID=:user AND searchname=:searchname");

    $stmt->bindParam(':user', $user, PDO::PARAM_STR);
    $stmt->bindParam(':searchname', $searchname, PDO::PARAM_STR);

    $stmt2 = $dbHandle->prepare("INSERT INTO searches (userID,searchname,searchfield,searchvalue) VALUES (:user,:searchname,:searchfield,:searchvalue)");

    $stmt2->bindParam(':user', $user, PDO::PARAM_STR);
    $stmt2->bindParam(':searchname', $searchname, PDO::PARAM_STR);
    $stmt2->bindParam(':searchfield', $searchfield, PDO::PARAM_STR);
    $stmt2->bindParam(':searchvalue', $searchvalue, PDO::PARAM_STR);

    $dbHandle->beginTransaction();

    $user = intval($_SESSION['user_id']);
    $searchname = 'advancedsearch#' . trim($_GET['searchname']);

    $stmt->execute();

    unset($_GET['_']);
    unset($_GET['savesearch']);
    unset($_GET['searchname']);
    reset($_GET);
    while (list($key, $field) = each($_GET)) {
        if (is_array($_GET[$key])) {
            while (list(, $value2) = each($_GET[$key])) {
                $searchfield = $key . '[]';
                $searchvalue = $value2;
                $stmt2->execute();
            }
        } elseif ($key != 'from' && $key != 'limit' && $key != 'display' && $key != 'orderby' && !empty($_GET[$key])) {
            $searchfield = $key;
            $searchvalue = $field;
            $stmt2->execute();
        }
    }

    $dbHandle->commit();
    die();
}

if (isset($_GET['loadsearch']) && !empty($_GET['searchname'])) {

    database_connect(IL_DATABASE_PATH, 'library');

    $stmt = $dbHandle->prepare("SELECT searchfield,searchvalue FROM searches WHERE userID=:user AND searchname=:searchname");

    $stmt->bindParam(':user', $user, PDO::PARAM_STR);
    $stmt->bindParam(':searchname', $searchname, PDO::PARAM_STR);

    $user = $_SESSION['user_id'];
    $searchname = "advancedsearch#" . trim($_GET['searchname']);

    $stmt->execute();

    $url_string_array = array();

    while ($search = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $url_string_array[] = urlencode($search['searchfield']) . '=' . urlencode($search['searchvalue']);
    }
    $url_string = join('&', $url_string_array);
    die($url_string);
}

if (isset($_GET['deletesearch']) && !empty($_GET['searchname'])) {

    database_connect(IL_DATABASE_PATH, 'library');

    $stmt = $dbHandle->prepare("DELETE FROM searches WHERE userID=:user AND searchname=:searchname");

    $stmt->bindParam(':user', $user, PDO::PARAM_STR);
    $stmt->bindParam(':searchname', $searchname, PDO::PARAM_STR);

    $user = $_SESSION['user_id'];
    $searchname = "advancedsearch#" . trim($_GET['searchname']);

    $stmt->execute();

    die('OK');
}

if (isset($_GET['renamesearch']) && !empty($_GET['searchname']) && !empty($_GET['searchname2'])) {

    database_connect(IL_DATABASE_PATH, 'library');

    $stmt = $dbHandle->prepare("UPDATE searches SET searchname=:searchname2 WHERE userID=:user AND searchname=:searchname");

    $stmt->bindParam(':user', $user, PDO::PARAM_STR);
    $stmt->bindParam(':searchname', $searchname, PDO::PARAM_STR);
    $stmt->bindParam(':searchname2', $searchname2, PDO::PARAM_STR);

    $user = $_SESSION['user_id'];
    $searchname = "advancedsearch#" . trim(urldecode($_GET['searchname']));
    $searchname2 = "advancedsearch#" . trim(urldecode($_GET['searchname2']));

    $stmt->execute();

    die('OK');
}

if (!isset($_GET['from'])) {
    $from = '0';
} else {
    settype($_GET['from'], "integer");
    $from = $_GET['from'];
}

if (!isset($_GET['project'])) {
    $project = '';
} else {
    $project = $_GET['project'];
}

if (!isset($_SESSION['limit'])) {
    $limit = $_SESSION['limit'] = 10;
} else {
    settype($_SESSION['limit'], "integer");
    $limit = $_SESSION['limit'];
}

if (!isset($_SESSION['orderby'])) {
    $orderby = $_SESSION['orderby'] = 'id';
} else {
    $orderby = $_SESSION['orderby'];
}

if (!isset($_SESSION['display'])) {
    $display = $_SESSION['display'] = 'summary';
} else {
    $display = $_SESSION['display'];
}

if (empty($_GET['select']) ||
        ($_GET['select'] != 'library' &&
        $_GET['select'] != 'shelf' &&
        $_GET['select'] != 'desk' &&
        $_GET['select'] != 'clipboard')) {

    $_GET['select'] = 'library';
}

if (!empty($_GET['searchmode'])) {

######## REGISTER SESSION VARIABLES ##############

    $get_array = array('anywhere', 'authors', 'affiliation', 'title', 'journal', 'secondary_title', 'tertiary_title', 'abstract', 'keywords',
        'year', 'fulltext', 'notes', 'pdfnotes', 'category', 'whole_words', 'case', 'rating', 'search_id', 'custom1', 'custom2', 'custom3', 'custom4',
        'search-metadata', 'search-pdfs', 'search-pdfnotes', 'search-notes', 'include-categories', 'reference_type');

    while (list($key, $index) = each($get_array)) {

        if (!empty($_GET[$index])) {

            ${$index} = $_GET[$index];
            $_SESSION['session_' . $index] = $_GET[$index];

            if ($index != 'category' &&
                    $index != 'year' &&
                    $index != 'whole_words' &&
                    $index != 'case' &&
                    $index != 'rating' &&
                    $index != 'search_id' &&
                    $index != 'search-metadata' &&
                    $index != 'search-pdfs' &&
                    $index != 'search-pdfnotes' &&
                    $index != 'search-notes' &&
                    $index != 'reference_type' &&
                    $index != 'include-categories') {

                ${$index} = $_GET[$index];
                if (!empty($_GET[$index . '_separator'])) {
                    ${$index . '_separator'} = $_GET[$index . '_separator'];
                    $_SESSION['session_' . $index . '_separator'] = $_GET[$index . '_separator'];
                }
            }
        } else {

            ${$index} = '';
            unset($_SESSION['session_' . $index]);

            if ($index != 'category' &&
                    $index != 'year' &&
                    $index != 'whole_words' &&
                    $index != 'case' &&
                    $index != 'rating' &&
                    $index != 'search_id' &&
                    $index != 'search-metadata' &&
                    $index != 'search-pdfs' &&
                    $index != 'search-pdfnotes' &&
                    $index != 'search-notes' &&
                    $index != 'reference_type' &&
                    $index != 'include-categories') {

                ${$index . '_separator'} = '';
                unset($_SESSION['session_' . $index . '_separator']);
            }
        }
    }

    // Store current table name hash in session.

    $table_name_hash = '';
    $table_name_array = $_GET;
    $table_name_array['orderby'] = $orderby;
    unset($table_name_array['_']);
    unset($table_name_array['from']);
    unset($table_name_array['limit']);
    unset($table_name_array['display']);
    $table_name_array['user_id'] = $_SESSION['user_id'];
    $table_name_array = array_filter($table_name_array);
    ksort($table_name_array);
    $table_name_hash = 'search_' . hash('crc32', json_encode($table_name_array));
    $_SESSION['display_files'] = $table_name_hash;

    session_write_close();

// FILE CACHING

    $db_change = database_change(array(
        'library',
        'shelves',
        'projects',
        'projectsusers',
        'projectsfiles',
        'filescategories',
        'notes',
        'annotations'
            ), array(
        'full_text'
            ), array('clipboard'));

    if (isset($_GET['from'])) {
        $cache_name = cache_name();
        cache_start($db_change);
    }

//READ PROJECTS

    database_connect(IL_DATABASE_PATH, 'library');

    $desktop_projects = array();
    $desktop_projects = read_desktop($dbHandle);

######### LIMITS ################
######### RATING ################

    $rating_search = '';
    $rating_searches = array();

    if (!isset($_GET['rating'])) {
        $_GET['rating'][] = 1;
        $_GET['rating'][] = 2;
        $_GET['rating'][] = 3;
    } else {
        $ratings = array(1, 2, 3);
        $missing_ratings = array_diff($ratings, $_GET['rating']);
        foreach ($missing_ratings as $missing_rating) {
            $rating_searches[] = 'rating!=' . intval($missing_rating);
        }
        if (!empty($rating_searches)) {
            $rating_search = join(' AND ', $rating_searches);
            $rating_search = $rating_search . ' AND ';
        }
    }

######### PUBLICATION TYPE ################

    $type_search = '';
    if (!empty($_GET['reference_type'])) {

        $type_quoted = $dbHandle->quote($_GET['reference_type']);
        $type_search = 'reference_type=' . $type_quoted . ' AND';
    }

######### CATEGORIES ################

    $category_search = '';

    if (isset($_GET['category'])) {
        $category_array_unassigned = array();
        if (in_array('NA', $_GET['category'])) {
            if (!isset($_GET['include-categories']) || (isset($_GET['include-categories']) && $_GET['include-categories'] == 1)) {
                $result = $dbHandle->query("SELECT id FROM library WHERE id NOT IN (SELECT fileID FROM filescategories)");
            } elseif (isset($_GET['include-categories']) && $_GET['include-categories'] == 2) {
                $result = $dbHandle->query("SELECT DISTINCT fileID FROM filescategories");
            }
            $category_array_unassigned = $result->fetchAll(PDO::FETCH_COLUMN);
            $result = null;
        }
        $categories_array = array_diff($_GET['category'], array('NA'));
        $categories = join(',', $categories_array);
        if (preg_match('/[^\d\,]/', $categories) > 0)
            $categories = '';
        if (!isset($_GET['include-categories']) || (isset($_GET['include-categories']) && $_GET['include-categories'] == 1)) {
            $result = $dbHandle->query("SELECT DISTINCT fileID FROM filescategories WHERE categoryID IN (" . $categories . ")");
        } elseif (isset($_GET['include-categories']) && $_GET['include-categories'] == 2) {
            $result = $dbHandle->query("SELECT id FROM library WHERE id NOT IN (SELECT fileID FROM filescategories WHERE categoryID IN (" . $categories . "))
                                        EXCEPT SELECT id FROM library WHERE id NOT IN (SELECT fileID FROM filescategories)");
        }
        $category_array = $result->fetchAll(PDO::FETCH_COLUMN);
        $category_array = array_merge($category_array, $category_array_unassigned);
        $category_string = implode(',', $category_array);
        $category_search = "id IN (" . $category_string . ") AND ";
        $category_array = null;
        $result = null;
    }

######### SHELF, CLIPBOARD, DESK ################

    $in = '';

    if ($_GET['select'] == 'shelf') {
        $in = "id IN (SELECT fileID FROM shelves WHERE userID=" . intval($_SESSION['user_id']) . ") AND";
//        $in = "INNER JOIN shelves ON library.id=shelves.fileID WHERE shelves.userID=" . intval($_SESSION['user_id']) . " AND";
    }

    if ($_GET['select'] == 'desk') {
        $project_name = '';
        $in = 'id IN () AND ';
        if (is_numeric($_GET['project'])) {
            $result = $dbHandle->query("SELECT fileID FROM projectsfiles WHERE projectID=" . intval($_GET['project']));
            $project_files = $result->fetchAll(PDO::FETCH_COLUMN);
            $project_files = implode(',', $project_files);
            $in = "id IN (" . $project_files . ") AND";
            $result = null;
            $result = $dbHandle->query("SELECT project FROM projects WHERE projectID=" . intval($_GET['project']));
            $project_name = $result->fetchColumn();
            $result = null;
        }
    }

    if ($_GET['select'] == 'clipboard') {
        $in = "id IN (SELECT id FROM clipboard.files) AND";
    }

    $ordering = 'ORDER BY ' . $orderby . ' COLLATE NOCASE ASC';
    if ($orderby == 'year' || $orderby == 'addition_date' || $orderby == 'rating' || $orderby == 'id')
        $ordering = 'ORDER BY ' . $orderby . ' DESC';
    if ($orderby == 'id')
        $ordering = '';

    $anywhere_array = array();
    $authors_array = array();
    $title_array = array();
    $journal_array = array();
    $secondary_title_array = array();
    $abstract_array = array();
    $keywords_array = array();
    $affiliation_array = array();
    $search_id_array = array();
    $year_array = array();
    $fulltext_array = array();
    $notes_array = array();
    $search_array = array();
    $case2 = $case;
    if (empty($case))
        $case2 = 0;
    $rating_array = array();

    if (isset($_GET['searchmode']) && $_GET['searchmode'] == 'quick') {
        include 'quicksearch.php';
    } elseif (isset($_GET['searchmode']) && $_GET['searchmode'] == 'advanced') {
        include 'advancedsearch.php';
    } elseif (isset($_GET['searchmode']) && $_GET['searchmode'] == 'expert') {
        include 'expertsearch.php';
    }
    $search_query_array = array_merge(
            (array) $anywhere_array,
            (array) $authors_array,
            (array) $journal_array,
            (array) $secondary_title_array,
            (array) $affiliation_array,
            (array) $keywords_array,
            (array) $title_array,
            (array) $abstract_array,
            (array) $year_array,
            (array) $search_id_array,
            (array) $fulltext_array,
            (array) $notes_array,
            (array) $search_array);

    $search_query = join(' ', $search_query_array);

//SEARCH

    $result_array = array();
    $rows = 0;

    if (!empty($search_string) && $_GET['searchtype'] == 'metadata') {

        if ($_GET['select'] == 'shelf') {
            perform_search("SELECT id FROM library INNER JOIN shelves ON library.id=shelves.fileID WHERE shelves.userID=" . intval($_SESSION['user_id']) . " AND $rating_search $type_search $category_search $search_string $ordering");
        } else {
            perform_search("SELECT id FROM library WHERE $in $rating_search $type_search $category_search $search_string $ordering");
        }
        
    } elseif (!empty($search_string) && $_GET['searchtype'] == 'notes') {

        $notes_in = str_replace("id IN", "fileID IN", $in);
        $notes_category_search = str_replace("id IN", "fileID IN", $category_search);

        $dbHandle->sqliteCreateFunction('search_strip_tags', 'sqlite_strip_tags', 1);

        if ($_GET['select'] == 'shelf') {
            $notes_query = "SELECT fileID FROM notes INNER JOIN shelves USING (fileID,userID) WHERE shelves.userID=" . intval($_SESSION['user_id']) . " AND $notes_category_search $search_string";
        } else {
            $notes_query = "SELECT fileID FROM notes WHERE $notes_in userID=" . intval($_SESSION['user_id']) . " AND $notes_category_search $search_string";
        }

        perform_search("SELECT id FROM library WHERE $rating_search $type_search id IN ($notes_query) $ordering");

    } elseif (!empty($search_string) && $_GET['searchtype'] == 'pdfnotes') {

        $notes_query = "SELECT filename FROM annotations WHERE userID=" . intval($_SESSION['user_id']) . " AND $search_string";

        if ($_GET['select'] == 'shelf') {
            perform_search("SELECT id FROM library INNER JOIN shelves ON library.id=shelves.fileID WHERE shelves.userID=" . intval($_SESSION['user_id']) . " AND $rating_search $type_search $category_search file IN ($notes_query) $ordering");
        } else {
            perform_search("SELECT id FROM library WHERE $in $rating_search $type_search $category_search file IN ($notes_query) $ordering");
        }

    } elseif ($_GET['searchtype'] == 'pdf') {

        $dbHandle = null;

        $fulltext_in = str_replace("id IN", "fileID IN", $in);
        $fulltext_category_search = str_replace("id IN", "fileID IN", $category_search);

        database_connect(IL_DATABASE_PATH, 'fulltext');

        $dbHandle->sqliteCreateFunction('regexp_match', 'sqlite_regexp', 3);

        if ($case == 1)
            $dbHandle->exec("PRAGMA case_sensitive_like = 1");

        ###no index search
        if ($_GET['select'] == 'shelf') {
            $result = $dbHandle->query("SELECT fileID FROM full_text WHERE fileID IN (" . join(',', $shelf_files) . ") AND $fulltext_category_search $search_string");
        } else {
            $result = $dbHandle->query("SELECT fileID FROM full_text WHERE $fulltext_in $fulltext_category_search $search_string");
        }

        $result_ids = array();
        if ($result)
            $result_ids = $result->fetchAll(PDO::FETCH_COLUMN);
        $result = null;
        $dbHandle = null;

        $result_string = join(",", $result_ids);
        $result_string = "id IN ($result_string)";

        database_connect(IL_DATABASE_PATH, 'library');
        perform_search("SELECT id FROM library WHERE $rating_search $type_search $result_string ORDER BY $orderby COLLATE NOCASE $ordering");

    }

//PRE-FETCH CATEGORIES, PROJECTS FOR DISPLAYED ITEMS IN A BATCH INTO TEMP DATABASE TO OFFLOAD THE MAIN DATABASE
    if ($rows > 0) {
        
        $result = $result->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $item) {

            $display_files_array[] = $item['id'];
        }

        $display_files2 = join(",", $display_files_array);
        
        // Read shelf files.
        $shelf_files = read_shelf($dbHandle, $display_files_array);
    
        // Read clipboard files.
        attach_clipboard($dbHandle);
        $clip_result = $dbHandle->query("SELECT id FROM clipboard.files WHERE id IN ($display_files2)");
        $clip_files = $clip_result->fetchAll(PDO::FETCH_COLUMN);
        $clip_result = null;
        
        try {
            $tempdbHandle = new PDO('sqlite::memory:');
        } catch (PDOException $e) {
            print "Error: " . $e->getMessage() . "<br/>";
            die();
        }
        $quoted_path = $tempdbHandle->quote(IL_DATABASE_PATH . DIRECTORY_SEPARATOR . 'library.sq3');
        $tempdbHandle->exec("ATTACH DATABASE $quoted_path AS librarydb");

        $tempdbHandle->beginTransaction();

        $tempdbHandle->exec("CREATE TABLE temp_categories (
                fileID integer NOT NULL,
                categoryID integer NOT NULL,
                category text NOT NULL)");
        $tempdbHandle->exec("CREATE TABLE temp_projects (
                fileID integer NOT NULL,
                projectID integer NOT NULL)");
        $tempdbHandle->exec("CREATE TABLE temp_notes (
                fileID integer NOT NULL,
                notesID integer NOT NULL,
                notes text NOT NULL)");

        $tempdbHandle->exec("INSERT INTO temp_categories SELECT fileID,filescategories.categoryID,category
                            FROM librarydb.categories INNER JOIN librarydb.filescategories ON filescategories.categoryID=categories.categoryID
                            WHERE fileID IN ($display_files2)");

        $tempdbHandle->exec("INSERT INTO temp_projects SELECT fileID,projectID
                            FROM librarydb.projectsfiles WHERE fileID IN ($display_files2)");

        if (isset($_SESSION['auth']))
            $tempdbHandle->exec("INSERT INTO temp_notes SELECT fileID,notesID,notes
                            FROM librarydb.notes WHERE fileID IN ($display_files2) AND userID=" . intval($_SESSION['user_id']));

        $tempdbHandle->commit();
        $tempdbHandle->exec("DETACH DATABASE librarydb");
    }

    $microtime2 = microtime(true);
    $microtime = $microtime2 - $microtime1;
    $microtime = sprintf("%01.2f sec", $microtime);

    if ($rows > 0) {

        print '<div id="display-content">';
        ?>
        <div class="alternating_row" style="padding:0.35em;padding-bottom: 0;border-bottom:1px solid #c5c6c8">
            <div id="exportbutton" class="ui-state-highlight ui-corner-all" style="display:inline-block;padding:0.2em 0.4em">
                &nbsp;<i class="fa fa-briefcase"></i> Export&nbsp;
            </div>
            <div id="omnitoolbutton" class="ui-state-highlight ui-corner-all" style="display:inline-block;margin-left:2px;padding:0.2em 0.4em">
                &nbsp;<i class="fa fa-wrench"></i> Omnitool&nbsp;</div>
            <div class="ui-state-highlight ui-corner-all" style="display:inline-block;margin-left:2px;padding:0.2em 0.4em" id="printlist">
                &nbsp;<i class="fa fa-print"></i> Print&nbsp;
            </div>
            <div style="float:right;margin: 2px;margin-top: 0">
                <span style="position:relative;top:-7px">
                    Display
                </span>
                <select id="select-display" style="width:9em">
                    <option value="brief" <?php print $display == 'brief' ? 'selected' : ''; ?>>Title</option>
                    <option value="summary" <?php print $display == 'summary' ? 'selected' : ''; ?>>Summary</option>
                    <option value="abstract" <?php print $display == 'abstract' ? 'selected' : ''; ?>>Abstract</option>
                    <option value="icons" <?php print $display == 'icons' ? 'selected' : ''; ?>>Icons</option>
                </select>
                <span style="position:relative;top:-7px">
                    Order
                </span>
                <select id="select-order" style="width:11em">
                    <option value="id" <?php print $orderby == 'id' ? 'selected' : ''; ?>>Date Added</option>
                    <option value="year" <?php print $orderby == 'year' ? 'selected' : ''; ?>>Date Published</option>
                    <option value="journal" <?php print $orderby == 'journal' ? 'selected' : ''; ?>>Journal</option>
                    <option value="rating" <?php print $orderby == 'rating' ? 'selected' : ''; ?>>Rating</option>
                    <option value="title" <?php print $orderby == 'title' ? 'selected' : ''; ?>>Title</option>
                </select>
                <span style="position:relative;top:-7px">
                    Show
                </span>
                <select id="select-number" style="width:6em">
                    <option value="5" <?php print $limit == 5 ? 'selected' : ''; ?>>5</option>
                    <option value="10" <?php print $limit == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="15" <?php print $limit == 15 ? 'selected' : ''; ?>>15</option>
                    <option value="20" <?php print $limit == 20 ? 'selected' : ''; ?>>20</option>
                    <option value="50" <?php print $limit == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php print $limit == 100 ? 'selected' : ''; ?>>100</option>
                </select>
            </div>
            <div style="clear:both"></div>
        </div>
        <?php
    }

    if (isset($_GET['select']) && $_GET['select'] == 'shelf' && isset($_SESSION["auth"])) {
        $what = "Shelf";
    } elseif (isset($_GET['select']) && $_GET['select'] == 'desk') {
        $what = "Project: " . $project_name;
    } elseif (isset($_GET['select']) && $_GET['select'] == 'clipboard') {
        $what = "Clipboard";
    } else {
        $what = "Library";
    }

    if (!empty($search_query))
        print '<div id="list-title" style="font-weight: bold; padding: 6px;text-align:center">' . $what . ' &raquo; Query: ' . htmlspecialchars($search_query) . '</div>';

    $url_array = array();
    reset($_GET);
    while (list($name, $value) = each($_GET)) {
        if (is_array($_GET[$name])) {
            while (list(, $value2) = each($_GET[$name])) {
                if (!empty($value2))
                    $url_array[] = urlencode($name . '[]') . '=' . urlencode($value2);
            }
        } elseif ($name != 'from') {
            if (!empty($value))
                $url_array[] = urlencode($name) . '=' . urlencode($value);
        }
    }
    $url_string = join('&', $url_array);

    if ($rows > 0) {

        $items_from = $from + 1;
        (($from + $limit) > $rows) ? $items_to = $rows : $items_to = $from + $limit;

        print '<table cellspacing="0" class="top" style="margin-bottom:1px"><tr><td style="width: 18em">';

        print '<div class="ui-state-highlight ui-corner-top' . ($from == 0 ? ' ui-state-disabled' : '') . '" style="float:left;margin-left:2px;width:26px">'
                . ($from == 0 ? '' : '<a href="' . htmlspecialchars("search.php?$url_string&from=0") . '" class="navigation" style="display:block;width:26px">') .
                '&nbsp;<i class="fa fa-caret-left"></i> <i class="fa fa-caret-left"></i>&nbsp;'
                . ($from == 0 ? '' : '</a>') .
                '</div>';

        print '<div class="ui-state-highlight ui-corner-top' . ($from == 0 ? ' ui-state-disabled' : '') . '" style="float:left;margin-left:2px;width:4em">'
                . ($from == 0 ? '' : '<a title="Shortcut: A" class="prevpage navigation" href="' . htmlspecialchars("search.php?$url_string&from=" . ($from - $limit)) . '" style="color:black;display:block;width:100%">') .
                '<i class="fa fa-caret-left"></i>&nbsp;Back'
                . ($from == 0 ? '' : '</a>') .
                '</div>';

        print '</td><td style="text-align: center">Items ' . $items_from . '-' . $items_to . ' of <span id="total-items">' . $rows . '</span> in ' . $microtime . '.</td>';

        print '<td style="width: 19em">';

        (($rows % $limit) == 0) ? $lastpage = $rows - $limit - ($rows % $limit) : $lastpage = $rows - ($rows % $limit);

        print '<div class="ui-state-highlight ui-corner-top' . (($rows > ($from + $limit)) ? '' : ' ui-state-disabled') . '" style="float:right;margin-right:2px;width:26px">'
                . (($rows > ($from + $limit)) ? '<a class="navigation" href="' . htmlspecialchars("search.php?$url_string&from=$lastpage") . '" style="display:block;width:26px">' : '') .
                '<i class="fa fa-caret-right"></i>&nbsp;<i class="fa fa-caret-right"></i>'
                . (($rows > ($from + $limit)) ? '</a>' : '') .
                '</div>';

        print '<div class="ui-state-highlight ui-corner-top' . (($rows > ($from + $limit)) ? '' : ' ui-state-disabled') . '" style="float:right;margin-right:2px;width: 4em">'
                . (($rows > ($from + $limit)) ? '<a title="Shortcut: D" class="nextpage navigation" href="' . htmlspecialchars("search.php?$url_string&from=" . ($from + $limit)) . '" style="color:black;display:block;width:100%">' : '') .
                '&nbsp;Next <i class="fa fa-caret-right"></i>&nbsp;'
                . (($rows > ($from + $limit)) ? '</a>' : '') .
                '</div>';

        print '<div class="ui-state-highlight ui-corner-top pgdown" style="float: right;width: 4em;margin-right:2px">PgDn</div>';

        print '</td></tr></table>';

        show_search_results($result, $_GET['select'], $shelf_files, $desktop_projects, $clip_files, $tempdbHandle);

        print '<table cellspacing="0" class="top" style="margin:1px 0px 2px 0px"><tr><td style="width:50%">';

        print '<div class="ui-state-highlight ui-corner-bottom' . ($from == 0 ? ' ui-state-disabled' : '') . '" style="float:left;margin-left:2px;width:26px">'
                . ($from == 0 ? '' : '<a href="' . htmlspecialchars("search.php?$url_string&from=0") . '" class="navigation" style="display:block;width:26px">') .
                '<i class="fa fa-caret-left"></i> <i class="fa fa-caret-left"></i>'
                . ($from == 0 ? '' : '</a>') .
                '</div>';

        print '<div class="ui-state-highlight ui-corner-bottom' . ($from == 0 ? ' ui-state-disabled' : '') . '" style="float:left;margin-left:2px;width:4em">'
                . ($from == 0 ? '' : '<a title="Shortcut: A" class="navigation" href="' . htmlspecialchars("search.php?$url_string&from=" . ($from - $limit)) . '" style="color:black;display:block;width:100%">') .
                '<i class="fa fa-caret-left"></i> Back'
                . ($from == 0 ? '' : '</a>') .
                '</div>';

        print '</td><td style="width:50%">';

        print '<div class="ui-state-highlight ui-corner-bottom' . (($rows > ($from + $limit)) ? '' : ' ui-state-disabled') . '" style="float:right;margin-right:2px;width:26px">'
                . (($rows > ($from + $limit)) ? '<a class="navigation" href="' . htmlspecialchars("search.php?$url_string&from=$lastpage") . '" style="display:block;width:26px">' : '') .
                '<i class="fa fa-caret-right"></i> <i class="fa fa-caret-right"></i>'
                . (($rows > ($from + $limit)) ? '</a>' : '') .
                '</div>';

        print '<div class="ui-state-highlight ui-corner-bottom' . (($rows > ($from + $limit)) ? '' : ' ui-state-disabled') . '" style="float:right;margin-right:2px;width:4em">'
                . (($rows > ($from + $limit)) ? '<a title="Shortcut: D" class="navigation" href="' . htmlspecialchars("search.php?$url_string&from=" . ($from + $limit)) . '" style="color:black;display:block;width:100%">' : '') .
                'Next <i class="fa fa-caret-right"></i>'
                . (($rows > ($from + $limit)) ? '</a>' : '') .
                '</div>';

        print '<div class="ui-state-highlight ui-corner-bottom pgup" style="float:right;width:4em;margin-right:2px">PgUp</div>';

        print "\n  </td>\n </tr>\n</table>";
    } else {
        print '<div style="position:relative;top:43%;left:0;color:#bfbeb9;font-size:28px;width:100%;text-align:center"><b>No Hits</b></div>';
    }
    print '</div>';

    if (isset($_GET['from']))
        cache_store();
}
?>