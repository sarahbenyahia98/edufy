package com.pixelnx.eacademy.ui.newZoom;

import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import us.zoom.sdk.JoinMeetingOptions;
import us.zoom.sdk.JoinMeetingParams;
import us.zoom.sdk.MeetingService;
import us.zoom.sdk.ZoomSDK;
import us.zoom.sdk.ZoomSDKInitParams;
import us.zoom.sdk.ZoomSDKInitializeListener;

import androidx.appcompat.app.AppCompatActivity;

import com.pixelnx.eacademy.R;

public class elazoom extends AppCompatActivity {
    EditText Name,MN,MP;
    Button Join;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.elazoom);

        Name=findViewById(R.id.etname);
        MN=findViewById(R.id.etmn);
        MP=findViewById(R.id.etmp);
        Join=findViewById(R.id.btjoinmeeting);

        initilizeZoom(this);

        Join.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String MeetingNumber = MN.getText().toString();
                String MeetingPassword = MP.getText().toString();
                String UserName = Name.getText().toString();
                if (MeetingNumber.trim().length() > 0 && MeetingPassword.length() > 0 && UserName.length() > 0) {
                    joinMeeting(elazoom.this, MeetingNumber, MeetingPassword, UserName);
                } else {
                    Toast.makeText(elazoom.this, "Invalid Input", Toast.LENGTH_SHORT).show();
                }
            }
        });
    }
    private void initilizeZoom(Context context){
        ZoomSDK sdk=ZoomSDK.getInstance();
        ZoomSDKInitParams params=new ZoomSDKInitParams();
        params.appKey="4JSMgC9XdKM0YqIyjja5EPl8FnSX0XZwvYAh";
        params.appSecret="BwOSDEFHFHkuIaBEu4sunDtHIF3QNf4DKtlH";
        params.domain="zoom.us";
        params.enableLog=true;

        ZoomSDKInitializeListener listener=new ZoomSDKInitializeListener() {
            @Override
            public void onZoomSDKInitializeResult(int i, int i1) {

            }

            @Override
            public void onZoomAuthIdentityExpired() {

            }
        };
        sdk.initialize(context,listener,params);

    }


    private  void joinMeeting(Context context,String meetingNumber, String meetingPassword, String userName){
        MeetingService meetingService=ZoomSDK.getInstance().getMeetingService();
        JoinMeetingOptions options=new JoinMeetingOptions();
        JoinMeetingParams params=new JoinMeetingParams();
        params.displayName=userName;
        params.meetingNo=meetingNumber;
        params.password=meetingPassword;
        meetingService.joinMeetingWithParams(context,params,options);

    }
}
