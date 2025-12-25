<?php
include('includes/config_elasticsearch.php');
if(!empty($_POST["classid"])) 
{
 $cid=intval($_POST['classid']);
 if(!is_numeric($cid)){
 
 	echo htmlentities("invalid Class");exit;
 }
 else{
    // Elasticsearch query to get students by classId
    $query = [
        'query' => [
            'term' => ['classId' => $cid]
        ],
        'sort' => [
            'studentName.sortable' => 'asc'
        ],
        'size' => 500
    ];
    
    $result = $es->search(INDEX_STUDENTS, $query);
    
    ?><option value="">Select Category </option><?php
    if($result['success'] && isset($result['data']['hits']['hits'])) {
        foreach($result['data']['hits']['hits'] as $hit) {
            $row = $hit['_source'];
            ?>
            <option value="<?php echo htmlentities($row['studentId']); ?>"><?php echo htmlentities($row['studentName']); ?></option>
            <?php
        }
    }
 }

}
// Code for Subjects
if(!empty($_POST["classid1"])) 
{
 $cid1=intval($_POST['classid1']);
 if(!is_numeric($cid1)){
 
  echo htmlentities("invalid Class");exit;
 }
 else{
    $status=0;
    // Elasticsearch query to get subjects for a class from curriculum mappings
    $query = [
        'query' => [
            'bool' => [
                'must' => [
                    ['term' => ['classId' => $cid1]]
                ],
                'must_not' => [
                    ['term' => ['status' => $status]]
                ]
            ]
        ],
        'sort' => [
            'subjectName.sortable' => 'asc'
        ],
        'size' => 500
    ];
    
    $result = $es->search(INDEX_CURRICULUM_MAPPINGS, $query);
    
    if($result['success'] && isset($result['data']['hits']['hits'])) {
        foreach($result['data']['hits']['hits'] as $hit) {
            $row = $hit['_source'];
            ?>
            <p> <?php echo htmlentities($row['subjectName']); ?><input type="text"  name="marks[]" value="" class="form-control" required="" placeholder="Enter the Grade" autocomplete="off"></p>
            <?php
        }
    }
 }
}


?>

<?php

if(!empty($_POST["studclass"])) 
{
 $id= $_POST['studclass'];
 $dta=explode("$",$id);
$id=$dta[0];
$id1=$dta[1];
 
 // Elasticsearch query to check if result already exists
 $query = [
     'query' => [
         'bool' => [
             'must' => [
                 ['term' => ['studentId' => intval($id1)]],
                 ['term' => ['classId' => intval($id)]]
             ]
         ]
     ],
     'size' => 1
 ];
 
 $result = $es->search(INDEX_RESULTS, $query);
 
 $cnt=1;
 $totalHits = isset($result['data']['hits']['total']['value']) ? 
              $result['data']['hits']['total']['value'] : 
              (isset($result['data']['hits']['total']) ? intval($result['data']['hits']['total']) : 0);
 
 if($result['success'] && $totalHits > 0)
 { ?>
<p>
<?php
echo "<span style='color:red'> Result Already Declare .</span>";
 echo "<script>$('#submit').prop('disabled',true);</script>";
 ?></p>
<?php }


  }?>


