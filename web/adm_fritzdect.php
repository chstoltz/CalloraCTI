<?php

include('config.php');
include('session.php');
include('funktionen.php');
include('auth.php');
include('adm_check.php');
include('adm_header.php');
include('adm_menu.php');

if(isset($_GET['update'])) {
  $id = $_GET['update'];
  $x_dect->DectDoUpdate(new SoapParam($id, 'NewID'));
  $feedback = 'Update gestartet.';
}

if(isset($_POST['submit'])) {
  $id = $_POST['submit'];
  $phonebook = $_POST['phonebook'];
  $x_contact->SetDECTHandsetPhonebook(new SoapParam($id, 'NewDectID'), 
                                      new SoapParam($phonebook, 'NewPhonebookID'));
}

$list = explode(',', $x_contact->GetDECTHandsetList());

?>

<div class="w3-bar w3-grey">
  <div class="w3-bar-item w3-padding-large">Fritz!DECT</div>
</div>
<div class="w3-container">
  <ul class="w3-ul">
    <li class="w3-bar">
      <div class="w3-bar-item" style="width:50px">ID</div>
      <div class="w3-bar-item" style="width:50px">Nst.</div>
      <div class="w3-bar-item" style="width:150px">Name</div>
      <div class="w3-bar-item" style="width:150px">Modell</div>
      <div class="w3-bar-item" style="width:150px">Telefonbuch</div>
      <div class="w3-bar-item" style="width:100px">Update</div>
    </li>
    <?php
      if (isset($_GET['edit'])) {
        $edit_id = $_GET['edit'];
        $book_list = $x_contact->GetPhoneBookList();
        $array_book = explode(",",$book_list);
      }
      foreach($list as $item) {
        $handset = $x_contact->GetDECTHandsetInfo(new SoapParam($item, 'NewDectID'));
        $info = $x_dect->GetSpecificDectEntry(new SoapParam($item, 'NewID'));
        $phonebook_id = $handset['NewPhonebookID'];
        $pb = $x_contact->GetPhonebook(new SoapParam($phonebook_id, 'NewPhonebookID'));
        if($item == @$edit_id) {
        ?>
        <form action="adm_fritzdect.php" method="POST">
        <li class="w3-bar">
          <div class="w3-bar-item" style="width:50px"><?php echo $item; ?></div>
          <div class="w3-bar-item" style="width:50px"><?php echo $item+609; ?></div>
          <div class="w3-bar-item" style="width:150px"><?php echo $handset['NewHandsetName']; ?></div>
          <div class="w3-bar-item" style="width:150px"><?php echo $info['NewModel']; ?></div>
          <div class="w3-bar-item" style="width:150px">
            <select class="w3-select w3-padding" name="phonebook">
            <?php
              foreach($array_book as $book) {
                $bookname = $x_contact->GetPhonebook(new SoapParam($book, 'NewPhonebookID'));
                $bookname = $bookname['NewPhonebookName'];
                echo '<option value="'.$book.'">'.$bookname.'</option>';
              }
            ?>
            </select>
          </div>
          <div class="w3-bar-item" style="width:100px"><?php if($info['NewUpdateAvailable'] == 0) { echo '<i class="fa-solid fa-xmark"></i>'; } else { echo '<a href="adm_fritzdect.php?update='.$item.'"><i class="fa-solid fa-download"></i></a>'; } ?></div>
          <div class="w3-bar-item"><input type="hidden" name="submit" value="<?php echo $item;?>" />
          <button type="submit"><i class="fa-solid fa-floppy-disk" title="Speichern"></i></button></div>
        </li>
            </form>
        <?php
        } else {

    ?>
    <li class="w3-bar">
      <div class="w3-bar-item" style="width:50px"><?php echo $item; ?></div>
      <div class="w3-bar-item" style="width:50px"><?php echo $item+609; ?></div>
      <div class="w3-bar-item" style="width:150px"><?php echo $handset['NewHandsetName']; ?></div>
      <div class="w3-bar-item" style="width:150px"><?php if($info['NewModel'] == '') { echo '<i>unbekannt</i>'; } else { echo $info['NewModel']; } ?></div>
      <div class="w3-bar-item" style="width:150px"><?php echo $pb['NewPhonebookName']; ?></div>
      <div class="w3-bar-item" style="width:100px"><?php if($info['NewUpdateAvailable'] == 0) { echo '<i class="fa-solid fa-xmark"></i>'; } else { echo '<a href="adm_fritzdect.php?update='.$item.'"><i class="fa-solid fa-download"></i></a>'; } ?></div>
      <div class="w3-bar-item"><a href="adm_fritzdect.php?edit=<?php echo $item; ?>"><i class="fa-solid fa-pen" title="Editieren"></i></a></div>
    </li>
    <?php
      }
    }
    ?>
  </ul>
</div>
<?php
  include('footer.php');
?>