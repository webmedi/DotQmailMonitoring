<?php

class DotQmailMonitoring {

	const UPDATE_LOG_FILE = "dotQmailUpdate.log";
	const DOT_QMAIL_EXCUTE_PHP_CODE = "Insert Excute Code";

	/** .qmailファイルを指定する。 */
	private $targetDotQmailFile = null;

	public function __construct( ) {
		date_default_timezone_set( 'Asia/Tokyo' );
		$this->targetDotQmailFile = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Resources" . DIRECTORY_SEPARATOR . "file.txt";
		$this->UpdateFileFileCreate( __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Resources" . DIRECTORY_SEPARATOR . self::UPDATE_LOG_FILE, $this->targetDotQmailFile );
		$this->LastLineWrite( __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Resources" . DIRECTORY_SEPARATOR . self::UPDATE_LOG_FILE, $this->targetDotQmailFile, self::DOT_QMAIL_EXCUTE_PHP_CODE );

	}

	/**
	 * ファイルの最終行に書き込む。
	 */
	private function LastLineWrite( $path = null, $dotQmailFilePath = null, $str = null ) {
		if( $this->IsUpdateFileDiffCheck( $path,  $dotQmailFilePath ) && !$this->LogSearch( self::DOT_QMAIL_EXCUTE_PHP_CODE, file_get_contents( $dotQmailFilePath ), true ) ) {
			$fp = fopen( $dotQmailFilePath, 'a' );
			fwrite( $fp, PHP_EOL . $str . PHP_EOL );
			if( fclose( $fp ) ) {
				print $dotQmailFilePath;
				$nowTime = intval( $this->UpdateFileTime( $dotQmailFilePath ) ); // .qmail ファイルに書き込んだ時刻 UnixTime で格納する。
				// 数字だけの文字列の場合変換されない
				file_put_contents( $path, mb_convert_encoding( intval( $nowTime ), "UTF-8", "auto" ) );

			}

		}

	}

	/**
	 * ファイル存在を確認する。
	 */
	private function IsCheckFile( $path = null ) {
		$isFile = file_exists( $path );
		if( $isFile ) {
			return true;

		} else {
			print PHP_EOL . $path . " は存在していませんでした。" . PHP_EOL;
			return false;
		}

	}

	/**
	 * 更新日時差分比較用ファイルを作成する関数。
	 */
	private function UpdateFileFileCreate( $path = null, $dotQmailFilePath = null ) {
		$myDtTime = new DateTime( "2000-01-01" );
		if( !$this->IsCheckFile( $path ) ) {
			if( touch( $path ) ) {
				print PHP_EOL . $path . " を作成しました。" . PHP_EOL;
				if( !$this->LogSearch( self::DOT_QMAIL_EXCUTE_PHP_CODE, file_get_contents( $dotQmailFilePath ), true ) ) {
					// 数字だけの文字列の場合変換されない
					file_put_contents( $path, mb_convert_encoding( $myDtTime->getTimestamp( ), "UTF-8", "auto" ) );

				} else {
					print PHP_EOL . self::DOT_QMAIL_EXCUTE_PHP_CODE . " は既に, " . $dotQmailFilePath . " に含まれていたため古い時間で, " . $path . " に書き込みました。" . PHP_EOL;
					// 数字だけの文字列の場合変換されない
					file_put_contents( $path, mb_convert_encoding( $this->UpdateFileTime( $dotQmailFilePath ), "UTF-8", "auto" ) );

				}

			} else print PHP_EOL . $path . " を作成出来ませんでした。" . PHP_EOL;

		}

	}

	/**
	 * 更新日時差分比較用ファイルと.qmailファイルを比較して日付に差分があるかチェックする関数
	 */
	private function IsUpdateFileDiffCheck( $path = null, $dotQmailFilePath = null ) {
		$myDtUpdateFileTime = new \DateTime( );
		$myDtDotQmailFileTime = new \DateTime( );
		$myDtGosa = new \DateTime( );
		$myDtNow = new \DateTime( );
		$flg = true;

		$myDtUpdateFileTime->setTimestamp( intval( file_get_contents( $path ) ) );
		$myDtDotQmailFileTime->setTimestamp( $this->UpdateFileTime( $dotQmailFilePath ) );
		$myDtGosa->setTimestamp( strtotime( '+30 second' ) );

		$myInterval = $myDtUpdateFileTime->diff( $myDtDotQmailFileTime );

		var_dump( $myInterval->format( "%Y-%m-%d %H:%i:%s" ) );
		var_dump( $myDtDotQmailFileTime == $myDtUpdateFileTime );

		print PHP_EOL . "更新時刻( dotQmailUpdate ) : " . $myDtUpdateFileTime->format( "Y-m-d H:i:s" ) . PHP_EOL;
		print PHP_EOL . "更新時刻( qmail ) : " . $myDtDotQmailFileTime->format( "Y-m-d H:i:s" ) . PHP_EOL;
		print PHP_EOL . $myDtNow->format( "Y-m-d H:i:s" ) . PHP_EOL;
		print PHP_EOL . $myDtGosa->format( "Y-m-d H:i:s" ) . PHP_EOL;

		if( $myDtDotQmailFileTime != $myDtUpdateFileTime && $myDtDotQmailFileTime->getTimestamp( ) <= $myDtGosa->getTimestamp( ) ) {
			print PHP_EOL . ".qmail ファイルの更新時刻に差異がありました。" . PHP_EOL;
			return true;

		} else {
			print PHP_EOL . ".qmail ファイルの更新時刻に差異はありませんでした。" . PHP_EOL;
			$flg = false;
			return false;

		}

		if( !$this->LogSearch( self::DOT_QMAIL_EXCUTE_PHP_CODE, file_get_contents( $dotQmailFilePath ) ) && !$flg ) {
			print PHP_EOL . ".qmail ファイルの更新時刻に差異はありませんでしたが, " . self::DOT_QMAIL_EXCUTE_PHP_CODE . " の記述がありませんでしたので更新しました。" . PHP_EOL;
			return true;

		}

	}


	/**
	 * ファイルの更新日時を取得する。
	 */
	private function UpdateFileTime( $path = null, $debug = false ) {
		$myDt = new \DateTime( );
		if( $this->IsCheckFile( $path ) ) {
			$myDt->setTimestamp( filemtime( $path ) );
			if( $debug ) {
				$time = $path . " " . date_format( $myDt, "Y-m-d H:i:s" );
				print PHP_EOL . $time . PHP_EOL;

			}

		}
		return $myDt->getTimestamp( );

	}

	/**
	 * ログファイルの文字データからマッチしたか否かを判定します
	 *
	 * @param string $w 検索したい文字列を指定します
	 * @param string $logData ログファイルの文字データを指定します
	 * @return boolean $isMatch 対象ログから指定した文字列がマッチした場合, true, しなかった場合, false を返します
	 */
	public function LogSearch( $w = null, $logData = null, $debug = 0 ) {
		$isMatch = 0; // マッチしたか格納する変数
		try {
			//if( is_null( $w ) || is_null( $logData ) ) throw new \Exception( "引数エラー 引数が足りないもしくは値が空です。" );
			if( strpos( $logData, $w ) !== false ) {
				$isMatch = 1;
				if( $debug ) print PHP_EOL . "<p>" . $w . " は引数 logData に含まれていました。" . "</p>" . PHP_EOL;

			} else if( $debug ) print PHP_EOL . "<p>" . $w . " は引数 logData に含まれていませんでした。" . "</p>" . PHP_EOL;

			return $isMatch;

		} catch( \Exception $e ) {
			//$this->ProgramErrorLog( $e->getMessage( ) . ", " . $e->getTraceAsString( ) );

		}

	}

}

?>